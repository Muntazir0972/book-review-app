<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $data){

        $books = Book::withCount('reviews')->withSum('reviews','rating')->orderBy('created_at','DESC');

        if (!empty($data->keyword)) {
            $keyword = $data->keyword;
                $books->where(function($query) use ($keyword){                   
                    $query->where('title','like','%'.$keyword.'%')
                    ->orWhere('author','like','%'.$keyword.'%');
                });
        }

       $books = $books->where('status',1)->paginate(8); 
       return view('home',compact('books'));
    }

    public function detail($id){

        $book = Book::with(['reviews.user','reviews' => function($query){
            $query->where('status',1);
        }])->withCount('reviews')->withSum('reviews','rating')->findOrFail($id);

        if ($book->status == 0) {
            abort(404);
        }

        $relatedBooks = Book::where('status',1)
                            ->withCount('reviews')
                            ->withSum('reviews','rating')
                            ->take(3)->where('id','!=',$id)
                            ->inRandomOrder()
                            ->get();
        return view('book-detail',compact('book','relatedBooks'));
    }

    public function saveReview(Request $data){
        $validator = Validator::make($data->all(),[
            'review' => 'required|min:10',
            'rating' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }

        $countReview = Review::where('user_id',Auth::user()->id)->where('book_id',$data->book_id)->count();

        if ($countReview > 0) {
            session()->flash('error','You already submitted a review.');
            return response()->json([
                'status' => true,
            ]);

        }

        $review = new Review();
        $review->review  = $data->review;
        $review->rating  = $data->rating;
        $review->user_id = Auth::user()->id;
        $review->book_id = $data->book_id;
        $review->save();

        session()->flash('success','Review submitted sucessfully');
        return response()->json([
            'status' => true,
        ]);
    }
}
