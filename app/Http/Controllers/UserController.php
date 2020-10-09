<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\User;
use JWTAuth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTFactory;
use Illuminate\Support\Facades\Mail;
use App\Mail\Signupverify;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class UserController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */

    public function __construct()
    {
        //$this->mystring =$data;
    }
 
    public function register(Request $request)
    {
       //validating the incoming request
        $this->validate($request, [
            'name' => 'required|string', //max
            'email' => 'required|email|unique:users',
            'password' => ['required','confirmed','min:6','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/'], // should contain 8 characters, min 1 number, min A
        ]);
        
        $query = User::where('email',$request->input('email'))->get();
        if(count($query) != 0)
        {
            return response()->json(['message' => 'Email already exist!']);
        }

        $user = new User;
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $Password = $request->input('password');
        //app('hash')->$password;
        $user->password = Hash::make($Password);
        $user->role='normal';
        $user->verify_status='No';
        $token = rtrim(base64_encode(md5(microtime())),"=");
        $user->token = $token;
        $user->save();
        // return response()->json(['message' => 'sucess']);
         Mail::to($request->email)->send(new Signupverify($user));
        
        return response()->json(['message' => 'Verify your email!']);
    }
                
    public function verify(Request $request)
    {
        $this->validate($request, [
            'name'  => 'required|string',
            'token' => 'required',
        ]);
        
        $name = $request->input('name');
        $token = $request->input('token');
        
        $value = DB::table('users')->where([['name', $name],['token', $token]])->get();
        
        if(count($value) > 0)
        {
            if($value[0]->verify_status != 'Yes')
            {
                DB::table('users')
                ->where('token', $token)
                ->update(['verify_status' => 'Yes', 'email_verified_at' => Carbon::now()->toDateTimeString()]);
                
                return response()->json(['message' => 'Verification done!']);
            }
            else
            {
                return response()->json(['message' => 'Already Verified!']);
            }
        }
        else
        {
            return response()->json(['message' => 'Please Login!']);
        }
    }   
   
   
    //------------------------------------------------------------------------

    public function login(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $input = $request->only(['email', 'password']);
        // dd($input);
        $user = User::where(['email'=>$request->get('email')])->first();
        $role = $user->role;
        $email = $user->email;
        $name = $user->name;
        $token = Auth::attempt($input);
        //dd($card);
        if ($token==null){
            $code = 401;
                $output = [
                  'code' => $code,
                  'email'=>$email,
                  'name'=>$name,
                  'role'=> $role,
                  'message' =>'user is not authorized'
              ];
        } 
        else{
          $code = 201;
          $token = $this->respondWithToken($token);
          $output =[
            'code' => $code,
            'role'=> $role,
            'email'=>$email,
            'name'=>$name,
            'message' =>'user is logged in successfully & authorized',
            'token' => $token
          ];
        }

        return response()->json($output, $code);
        
    }

// //$payload = JWTFactory::make($input);
// //$token = JWTAuth::encode($payload);
// //-----------------------------------------------------------------------------------------------
//   //User::where(['email'=>$user_email])->select('email','id')->first(); 
//   //User:: where( 'id'>20 )->where('id'<30);

    // public function login(Request $request)
    // {
    //     //validate incoming request 
    //     $this->validate($request, [
    //         'email' => 'required|string',
    //         'password' => 'required|string',
    //     ]);

    //     $input = $request->only(['email', 'password']);
    //     // dd($input);
    //     $user_email = $request->get('email');
    //     $user_password = $request->get('password');
    //     $user = User::where(['email'=>$user_email, 'password'=>$user_password])->first();
    //     $password= $request->input('password');

    //     //for creating the token 
    //     $customClaims = ['preferred_username' => $user_email,
    //                       'exp'=> Auth::factory()->getTTL() * 60]; //user_id
        
    //     $payload = JWTFactory::make($customClaims);
    //     $token = JWTAuth::encode($payload);

    //     //dd($token_code);
    //     //$token = this->respondWithToken($token_code);
        
    //     if(!app('hash')->check($password, $user->password)){
    //       $code = 401;
    //       $output =[
    //         'code'=>$code, 
    //         'message' => "user is not authorized"];
    //     }
        
    //     else{
    //       $token = $this->respondWithToken($token);
    //       $code = 200;
    //       $output=['code'=> $code, 
    //       'token'=> $token,
    //       'message'=> "successfully logged in"];
    //     }
    //     return response()->json($output, $code);  

    //  }
        //-----------------------  
    public function resetPassword(Request $request){
        $this->validate($request, [
            'email'=>'required',
            'Old_Password' => 'required',
            'New_Password' => ['required','confirmed','min:6','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/']
            // 'password_confirm' => 'required|same:password'
        ]);
        $user = User::where(['email'=>$request->get('email')])->first();
        if($user == null) return "Email incorrect.";
        $user_OldPwd = $request->get('Old_Password');
        $user_NewPwd = $request->get('New_Password');
        if($user_OldPwd === $user_NewPwd) {
            return "New Password can't be same as the old password.";
        }
        
        if(!Hash::check($user_OldPwd, $user->password)){
            return "Old password you have entered is incorrect.";
        }
        $user->password = Hash::make($user_NewPwd);
        $user->save();
        return "New Password updated";
    }
//  //--------------------------------------------------------------------------
   // public function forgotpassword(Request $request){
   //  $this->validate($request,[
   //      'email'=>'required'
     
    public function forgot(Request $request, $token){
        $user = User::where('token', $token)->first();
        if($user!=null){
            $this->validate($request,[
                'New_Password'=>['required','min:6','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/'] 
            ]);

            if(Hash::check($request->get('New_Password'), $user->password)){
                return response()->json(['message'=>"New Password cannot be same as the old password."],403);
            }
            $user->password = app('hash')->make($request->get('New_Password'));
            $user->save();
            return response()->json(['user'=>$user],201);
        } else {
            return response()->json(['message'=>'Token is tampered'],401);
        }
    }

//------------------------------------------------------------------------------------
    public function checkvalidity(Request$request){
         $this->validate($request, [
            'email'=>'required'
            ]);
        $email= $request->get('email');
        $user = User::where('email', $email)->first();
        if($user!=null){
            return "user is valide";
        }
        else{
            return "user not found";
        }
        
    }
//------------------------------------------------------------------------------------
public function createpassword(Request $request){
    //validate the token and name/email
    $user = User::where([['token',$request->token]])->first();
    if($user!=null){
    $this->validate($request,[
        'New_password'=>['required','min:6','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/']
    ]);
    $password= $request->input('New_password');
    $user->password = Hash::make($password);
    $user->save();
    return response()->json(['user'=>$user],201);
    }

    else{
        return response()->json(['message'=> 'Token is tampered'],401);
    }
    

}

}
 

