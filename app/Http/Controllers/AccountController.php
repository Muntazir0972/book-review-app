<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
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

    
}
