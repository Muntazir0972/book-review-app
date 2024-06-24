<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Book;

class BookController extends Controller
{
    public function index(Request $data){

        $books = Book::orderBy('created_at','DESC');

        if (!empty($data->keyword)) {
            $books->where('title','like','%'.$data->keyword.'%');

        }
        $books = $books->paginate(10);
        return view('books.list',compact('books'));
    }

    public function create(){
        return view('books.create');
    }

    public function store(Request $data){

        $rules=[
            'title' => 'required|min:5',
            'author' => 'required|min:3',
            'status' => 'required',
        ];

        if (!empty($data->image)) {
            $rules['image']='image';
        }

        $validator=Validator::make($data->all(),$rules);

        if ($validator->fails()) {
            return redirect()->route('books.create')->withInput()->withErrors($validator);
        }

        $book = new Book();
        $book->title = $data->title;
        $book->description = $data->description;
        $book->author= $data->author;
        $book->status = $data->status;
        $book -> save();

        if (!empty($data->image)) {
            $image=$data->image;
            $ext=$image->getClientOriginalExtension();
            $imageName= time().'.'.$ext;
            $image->move(public_path('uploads/books'),$imageName);
            $book->image=$imageName;
            $book->save();
        }

        return redirect()->route('books.index')->withInput()->withErrors($validator)->with('success','Book Added Succesfully.');


    }

    public function edit($id){
        $book = Book::findOrFail($id);
        return view('books.edit',compact('book'));
    }

    public function update(Request $data,$id){

        $book = Book::findOrFail($id);

        $rules=[
            'title' => 'required|min:5',
            'author' => 'required|min:3',
            'status' => 'required',
        ];

        if (!empty($data->image)) {
            $rules['image']='image';
        }

        $validator=Validator::make($data->all(),$rules);

        if ($validator->fails()) {
            return redirect()->route('books.edit',$book->id)->withInput()->withErrors($validator);
        }

        $book->title = $data->title;
        $book->description = $data->description;
        $book->author= $data->author;
        $book->status = $data->status;
        $book -> save();

        if (!empty($data->image)) {

            File::delete(public_path('uploads/books/'.$book->image));

            $image=$data->image;
            $ext=$image->getClientOriginalExtension();
            $imageName= time().'.'.$ext;
            $image->move(public_path('uploads/books'),$imageName);
            $book->image=$imageName;
            $book->save();
        }

        return redirect()->route('books.index')->withInput()->withErrors($validator)->with('success','Book Updated Succesfully.');

    }

    public function destroy(Request $data){
        $book = Book::find($data->id);

        if ($book == null) {
            session()->flash('error','Book not found');
            return response()->json([
                'status' => false,
                'message' => 'Book not found'
            ]);
        }else{
            File::delete(public_path('uploads/books/'.$book->image));
            $book->delete();

            session()->flash('success','Book Deleted Successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Book Deleted Successfully'
            ]);
        }
    }
}
