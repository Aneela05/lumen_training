<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\User;
// use App\Mail\Resetpassword;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Auth;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\Mail;
// use App\Models\Forgotpassword;
// use Tymon\JWTAuth\JWTAuth;
// use Illuminate\Support\Facades\DB;


// class ForgotPswdController extends Controller
// {
//     /**
//      * Create a new controller instance.
//      *
//      * @return void
//      */
//     public function __construct()
//     {
//         //
//     }
// public function Createpassword(Request $request){

//         // validate data
//         $this->validate($request, [
//             'email' => 'required|email',
            
//         ]);
       
//         $email = $request->input('email');

//         $query = User::where('email',$email)->first();

//         if($query!=null)
//         {

//             $token = rtrim(base64_encode(md5(microtime())),"=");

//             $user = new Forgotpassword;

//             $user->token = $token;
//             //later this token is used for checking the user
//             $user->email = $email;

//             $Password= $request->input('password');

//             $user->password = Hash::make($Password);
//             $user->save();
//             //mail  is now sending to user email to verify 
//             Mail::to($request->email)->send(new Resetpassword($user));
//         }
       
//         return response()->json(['message','Check your email to reset your password']);

//     }

// }


       

   