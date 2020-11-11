<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\User;
use App\Forgot;
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
use App\Mail\Resetpassword;
use App\Mail\Createpassword;
use Carbon\Carbon;
use App\Jobs\ProcessPodcast;


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
        
    }
 
    public function register(Request $request)
    {
       
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required','confirmed','min:8','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/'], 
        ]);
        
        $value = User::where('email', $request->input('email'))->first();
        if($value!=null){
            return response()->json(['message'=>'email already exists']);
        }
        
        $user = new User;
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $Password = $request->input('password');
        $user->password = Hash::make($Password);
        $user->role='normal';
        $user->verify_status='No';
        $token = rtrim(base64_encode(md5(microtime())),"=");
        $user->token = $token;
        $user->save();
         $mailable = new Signupverify($user);
         $this->dispatch(new ProcessPodcast($user->email,$mailable));        
        return response()->json(['message' => 'Verify your email!']);
    }
                
    public function verify(Request $request,$token)
    {
        $value = DB::table('users')->where(['token', $token])->first();
        if($value==null){
            return "tampered";
        }
            if($value->verify_status != 'yes') 
            {
                $value->update(['verify_status' => 'yes', 'email_verified_at' => Carbon::now()->toDateTimeString()]);
                
                return response()->json(['message' => 'Verification done!']);
            }
            else
            {
                return response()->json(['message' => 'Already Verified!']);
            }
    }  
   
   
    //------------------------------------------------------------------------

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);
        $input = $request->only(['email', 'password']);
        $email = $request->input('email');
        $user = User::where(['email'=>$email])->first();
        $role = $user->role;
        $id = $user->id;
        $email = $user->email;
        $name = $user->name;
        $token = Auth::attempt($input);
        if ($token==null){
            return response()->json(['message'=>'check your mail'],401);
        } 
        else{
          $code = 201;
          $token = $this->respondWithToken($token);
          $output =[
            'id'=>$id,
            'code' => $code,
            'role'=> $role,
            'email'=>$email,
            'name'=>$name,
            'message' =>'user is logged in successfully & authorized',
            'token' => $token
          ];
          return response()->json($output, $code);
        }
        
    }

    public function resetPassword(Request $request){
        $this->validate($request, [
            'email'=>'required',
            'old_password' => 'required',
            'new_password' => ['required','confirmed','min:8','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/']
            
        ]);
        $user = User::where(['email'=>$request->get('email')])->first();
        if($user == null) return "Email incorrect.";
        $user_old_password = $request->input('old_password');
        $user_new_password = $request->input('new_password');
        if($user_old_password === $user_new_password) {
            return "New Password and old password cant be the same .";
        }
        if(!Hash::check($user_old_password, $user->password)){
            return "Old password you have entered is incorrect.";
        }
        $user->password = Hash::make($user_new_password);
        $user->save();
        return "New Password updated";

    }
    
    public function forgot(Request $request){
            $this->validate($request,[
                'token'=>'required',
                'new_password'=>['required','min:6','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/'] 
            ]);
            $token = $request->input('token');
            $user = Forgot::where([['token',$token]])->get();
            if(count($user)>0){
                if($user[0]->reset_status!='yes'){
                    Forgot::where('token',$token)->update(['reset_status' => 'yes']);
                    $password = Hash::make($request->new_password);
                    User::where('email',$user[0]->email)->update(['password' => $password,]);
                    return response()->json(['message','Password reset successful'],200);
                }
                else{
                    return response()->json(['message'=>'reset already'],403);
                }
            }
            else{
                return response()->json(['message','please login']);
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
            Mail::to($email)->send(new Createpassword($user));
            return "user is valide and check your mail to create password";
        }
        else{
            return "user not found";
        }
    }
//------------------------------------------------------------------------------------
public function createpassword(Request $request){
    $this->validate($request,[
        'token'=>'required',
        'new_password'=>['required','min:6','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/']
    ]);
    $user = User::where([['token',$request->token]])->first();
    if($user!=null){
    $password= $request->input('new_password');
    $user->password = Hash::make($password);
    $user->save();
    return response()->json(['user'=>$user],201);
    }
    else{
        return response()->json(['message'=> 'Token is tampered'],401);
    }


}
public function forgotpassword(Request $request){
    $this->validate($request, ['email' => 'required',]);

        $user = User::where('email', $request->input('email'))->first();
        if($user!= null)
        {
            $token = rtrim(base64_encode(md5(microtime())),"=");
            $query = new Forgot;
            $query->token = $token;
            $query->email = $request->input('email');
            $query->save();
            // Mail::to($request->email)->send(new Resetpassword($query));
            $mailable = new Resetpassword($query);
            $this->dispatch(new ProcessPodcast($query->email,$mailable));
        }
        return response()->json(['message','Check your email to reset your password'], 200);
    }
}


 

