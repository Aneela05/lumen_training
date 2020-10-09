<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;
use App\Mail\Signupverify;
use App\Mail\Createpassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;

class ListController extends Controller{

    public function __construct(){
        $this->middleware('auth');
    }

    public function check_admin($user){
        $user->role=='admin';
    }
    public function show(){
    //    return Auth::user(); //--gives the authenticated user if we uses  user token in the authorization 
       //Auth::user()->where('role','admin')->get(); //if we use authenticated user's token it will give the list of users whos role is admin
    //   $user= Auth::user()->where('role','admin')->first(); 
    //   if($user){
    //       $normal_list = User::where('role','normal')->get();
    //       return $normal_list;
    //   }

      if(Auth::user()->role=='admin'){
        $users_list = User::where('role','normal')->orWhere('role','admin')->get();
        return $users_list;
      }
      else{
          return Auth::user();
      }
    }

    public function create(Request $request){
        if(Auth::user()->role=='admin'){
            $this->validate($request,[
                'name' =>'required',
                'email' => 'required|email|unique:users'
            ]);
            $user= new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = 'null';
            $user->role = 'normal';
            $token= $token = rtrim(base64_encode(md5(microtime())),"=");
            $user->token =$token;
            $user->verify_status='No';
            $user->created_by= Auth::user()->email;
            $user->save();
            // return response()->json(['message'=>'successfully created', 'created_by'=>Auth::user()->email],201);
        }
        // else{
        //     return response()->json(['message'=> 'normal user cant create the account beacuse you are not authorized' ],401);
        // }

        Mail::to($request->email)->send(new Signupverify($user));
        return response()->json(['message'=>'verify your mail!']);

        Mail::to($request->email)->send(new Createpassword($user));
        return response()->json(['message'=>'link has been sent to your mail to create your password!']);
    }
 
    public function delete($id){
        $user = User::find($id);
        if($user==null){
            response()->json(['message'=> 'user not exists'],400);
        }
        else{
            if(Auth::user()->role=='admin')
            {
              if($user->role==='admin')
              {
               return response()->json(['message'=> 'admin cant be deleted'],401);
              }
              else
              {
              $user->delete();
              $deleted_by = Auth::user()->email;
              response()->json(['message'=> 'successfully deleted the user'],200);
              }
           }
           else{
                response()->json(['message'=> 'unauthorized' ],401);
           }
        }

         
    }

}











