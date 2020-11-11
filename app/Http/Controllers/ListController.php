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
      if(Auth::user()->role=='admin'){
        $users_list = User::where('role','normal')->orWhere('role','admin')->get();
        return $users_list;
      }
      else{
          return Auth::user();
      }
    }
    
    public function list($id){
        if($id == Auth::id()){
            $user = User::find($id);
            return response()->json(['user'=>$user],200);
        }
        else {
            return response()->json(['message'=>'Token invalid'],401);
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
        }

        Mail::to($request->email)->send(new Signupverify($user));
        Mail::to($request->email)->send(new Createpassword($user));
        return response()->json(['message'=>'verify your mail!', 'user'=>$user]);
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

    public function searchfilters(Request $request){
        if(!Auth::user()){
            return response()->json(['message'=>'login please']);
        }
       
        $users = User::where('role','normal');
       
        if($request->has('name')){
            $users->where('name','like','%'.$request->name.'%');
        }
        if($request->has('email')){
            $users->where('email','like','%'.$request->email.'%');
        }
        if($request->has('token')){
            $users->where('token',$request->token);
        }
        if($request->has('created_by')){
            $users->where('created_by',$request->created_by);
        }
        return $users->get();
    }

}











