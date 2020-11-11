<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTFactory;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Auth;

use App\User;

class AdminController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function is_Admin($admin){
        return $admin->role == 'admin';
    }
    
    public function create(Request $request){
        if($this->is_Admin(Auth::user())){
            $this->validate($request, [
                'name' =>'required',
                'email' => 'required|email|unique:users'
                
            ]);
            $user = new User;
            $user->name = $request->get('name');
            $user->email = $request->get('email');
            $user->createdBy = Auth::user()->id;
            $user->save();
            return response()->json(['user' => $user], 201);

        } else return response()->json(['user'=>Auth::user(),'message'=>'Unauthorized.'],401);
    }

   

    
    public function delete($id){
        // check whether the id exists in the database
        $user = User::findOrFail($id); 

        if($user->role == 'admin')
        {
         return response()->json(['message'=>'Admin can not be deleted'],402);
        }
        $admin_user = User::where('role','admin')->first();
            $user->delete();
            $code = 200;
            $output = [
                'message' => "person is deleted",
                 "deleted by"=>$admin_user->email,
                 'code'=> $code
                ];      
        return $output;

    }

    public function searchfilters(Request $request){
        
        $admin= User::where('role',$request->input('role'))->get();
        return $admin;
    }
}
//