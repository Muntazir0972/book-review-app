<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index(Request $data){
        $reviews = Review::with('book', 'user')->orderBy('created_at', 'DESC');
    
        if (!empty($data->keyword)) {
            $keyword = $data->keyword;
            $reviews = $reviews->where(function($query) use ($keyword) {
                $query->where('review', 'like', '%' . $keyword . '%')
                      ->orWhereHas('user', function($query) use ($keyword) {
                          $query->where('name', 'like', '%' . $keyword . '%');
                      });
            });
        }
    
        $reviews = $reviews->paginate(10);
        return view('account.reviews.list', compact('reviews'));
    }
    
    public function edit($id){
        $review = Review::findOrFail($id);
        return view('account.reviews.edit',compact('review'));
    }

    public function updateReview($id,Request $data){

        $review  = Review::findORFail($id);

        $validator = Validator::make($data->all(),[
            'review'=> 'required',
            'status'=> 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.reviews.edit',$id)->withInput()->withErrors($validator);
        }

        $review->review = $data->review;
        $review->status = $data->status;
        $review->save();

        session()->flash('success','Review Updated Successfully');
        return redirect()->route('account.reviews',$id);
    }

    public function deleteReview(Request $data){

        $id = $data->id;

        $review = Review::find($id);

        if ($review == null) {
            session()->flash('error','Review not Found');
            return response()->json([
                'status' => false
            ]);
        }else{
            $review -> delete();

            session()->flash('success','Review Deleted successfully');
            return response()->json([
                'status'=> false
            ]);
        }
    }
}
