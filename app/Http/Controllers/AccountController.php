<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Review;
class AccountController extends Controller
{
    public function register(){
        return view('account.register');
    }

    public function processRegister(Request $data){

        $validator = Validator::make($data->all(),[

            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:5',
            'password_confirmation' => 'required',

        ]);

        if ($validator->fails()) {
            return redirect()->route('account.register')->withInput()->withErrors($validator);
        }   


        $user = new User();
        $user -> name = $data -> name;
        $user -> email = $data -> email;
        $user -> password = Hash::make($data -> password);
        $user -> save();

        return redirect()->route('account.login')->with('success','You have Registered succesfully.');

    }


    public function login(){
        return view('account.login');
    }

    public function authenticate(Request $data){

        $validator = Validator::make($data->all(),[
            'email' => 'required |email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.login')->withInput()->withErrors($validator);
        }

        if (Auth::attempt(['email' => $data->email ,'password' => $data->password])) {
            
            return redirect()->route('home');

        }else{
            return redirect()->route('account.login')->with('error','Either Email/Password is Incorrect.');
        }

    }

    public function profile(){

        $user = User::find(Auth::user()->id);
        return view('account.profile',compact('user'));
    }

    public function updateProfile(Request $data){

        $rules=[
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,'.Auth::user()->id.',id',
        ];

        if (!empty($data->image)) {
            $rules['image']='image';
        }

        $validator = Validator::make($data->all(),$rules);

        if ($validator->fails()) {
            return redirect()->route('account.profile')->withInput()->withErrors($validator);
        }

        $user = User::find(Auth::user()->id);
        $user->name=$data->name;
        $user->email=$data->email;
        $user->save();


        //image uploading
        if (!empty($data->image)) {
            $image = $data->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time().'.'.$ext;
            $image -> move(public_path('uploads/profile'),$imageName);

            $user->image=$imageName;
            $user -> save();
        }
        return redirect()->route('account.profile')->with('success','Profile Updated Successfully');

    }

    public function logout(){

        Auth::logout();
        return redirect()->route('account.login');
    }

    public function myReviews(Request $data){

        $reviews = Review::with('book')->where('user_id',Auth::user()->id);
        $reviews = $reviews->orderBy('created_at','DESC');

        if (!empty($data->keyword)) {
            $keyword = $data->keyword;
            $reviews = $reviews->where(function($query) use ($keyword){
                $query->where('review','like','%' . $keyword . '%')
                ->orwhereHas('book',function($query) use ($keyword){
                    $query->where('title','like','%' . $keyword . '%');
                });
            });
        }

        $reviews = $reviews->paginate(10);
        return view('account.my-reviews.my-reviews',compact('reviews'));
    }

    public function editReview($id){

        $review = Review::where([
            'id' => $id,
            'user_id' => Auth::user()->id
        ])->with('book')->first();

        return view('account.my-reviews.edit-reviews',compact('review'));

    }

    public function updateReview($id,Request $data){

        $review  = Review::findORFail($id);

        $validator = Validator::make($data->all(),[
            'review'=> 'required',
            'rating'=> 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.myReviews.editReview',$id)->withInput()->withErrors($validator);
        }

        $review->review = $data->review;
        $review->rating = $data->rating;
        $review->save();

        session()->flash('success','Review Updated Successfully');
        return redirect()->route('account.myReviews');
    }
}
