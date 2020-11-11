<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Task;
use App\User;
use JWTAuth;
use App\Mail\Sendtask;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTFactory;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessPodcast;
use Carbon\Carbon;
use DateTime;

class TaskController extends Controller{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */

    public function __construct()
    {
       $this->middleware('auth');
    }
 
    public function addtask(Request $request){
        $creator = Auth::user();
        $this->validate($request,[
            'title'=>'required|string|max:255',
            "description"=>'required|max:255',
            "duedate"=>'required|date', 
            "assignee"=>'required',  
        ]);
            $task = new Task;
            $task->title = $request->input('title');
            $task->description= $request->input('description');
            $task->assignee=$request->input('assignee');
            $task->status='assigned';
            $date= new Datetime($request->input('duedate'));
            $task->duedate = $date->format('Y-m-d H:i:s');
            $creator->tasks()->save($task);
            // $creator->tasks1()->save($tasks);          
            $id = $task->assignee;
            $user=User::find($id);
            $email= $user->email;
            
            $mailable = new Sendtask($task);
            $this->dispatch(new ProcessPodcast($email,$mailable));
            // $this->dispatch(new ProcessPodcast($useremail,$task));
            // Mail::to($useremail)->send(new Sendtask($task));
            return response()->json(['task'=>$task,'message'=>'task assigned & mail has been sent '],200);
    }
    
    

    public function taskupdate(Request $request){
        $this->validate($request,[
            'title'=>'required',
            'description'=>'required',   
            'duedate'=>'required|date',
            'assignee'=>'required',

        ]);
        //lets check for the id n corresponding id of the auth user,
        $task = Task::where('id', $request->input('id'))->first();
        if($task->user_id!=Auth::id()){
            return response()->json(["message"=>'not authorised to update'],401);
        }
        else{
            $task->title= $request->input('title');
            $task->description = $request->input('description');
            $task->assignee= $request->input('assignee');
            $date = new DateTime($request->get('duedate'));
            $task->duedate=$date->format('Y-m-d H:i:s');
            $task->save();
            return response()->json(['message'=>'task updated', 'task'=>$task]);
        }
    }
    public function statusupdate(Request $request){
        $this->validate($request,[
            'status'=>'required',   
        ]);

        $task= Task::where('id',$request->input('id'))->first();
        if(Auth::id()!=$task->assignee){
            return response()->json(['message'=>'not authorized'],401);
        }
        else{
            $task->status= $request->input('status');
            $task->save();
            return response()->json(["task"=>$task,"message"=>'status updated'],200);
        }
    }

    public function deletetask(Request $request,$id){
        $task= Task::find($id);
       
        if($task->user_id!=Auth::id()){
            return response()->json(["message"=>'not authorised to delete'],403);
        }
        else{
            $task->delete();
            $task->status='deleted';
            $task->save();
            return response()->json(['message'=>'deleted successfully'],200);
        }

    }
    
    public function taskdelete(Request $request){
        $task= Task::where('id',$request->input('id'))->first();
        if($task->user_id!=Auth::id()){
            return response()->json(['message'=>'not authorised'],401);
        }
        else{
            $task->delete();
            $task->satus='deleted';
            $task->save();
            return response()->json(['message'=>'deleted successfully'],200);
        }

    }

//------------------------------------------------------------admin
    public function alltasks(Request $request){
        if(Auth::user()->role=="admin"){
            $tasks = Task::orderby('duedate')->with('assigneeUser')->get();
            // $task = Task::orderBy('duedate')->get();
            return $tasks;
        }
        else{
            return reponse()->json(['message'=>'not authorised'],401);
        }
        
    }
//--------------------------------------------------------------
    public function tasks(Request $request){
        $tasks = Task::where('assignee',Auth::id())->orderBy('dueDate')->get();
        return response()->json(['tasks'=>$tasks],200);
    }

    public function assignedtasks(Request $request){
        $user = Auth::id();
        $tasks = User::find($user)->tasks()->get();
        dd($tasks);
        return response()->json(['tasks'=>$tasks],200);
    }

    public function searchtasks(Request $request){
        $user = Auth::user();
        $assignee = Auth::id();
        if($user->role != "Admin")
        {
            $tasks = $tasks->where('assignee', $assignee);
        }
        
         if($request->has('title')){
             $tasks->where('title','like','%'.$request->title.'%');
         }
         if($request->has('description')){
            $tasks->where('description','like','%'.$request->description.'%');
        }
        if($request->has('assignee')){
            $tasks->where('assignee',$request->assignee);
        }
        if($request->has('status')){
            $tasks->where('status',$request->status);
        }
        if($request->has('duedate')){
            $tasks->where('duedate',$request->duedate);
        }
        if($request->has('user_id')){
            $tasks->where('user_id',$request->user_id);
        }
        return $tasks->get();
        
    }


    public function gettingtasks(Request $request){
        $tasks = DB::table('tasks')->where('assignee',Auth::id())->get();
        return $tasks; 
    }

    public function charts(Request $request){
        $assigned = Task::where('status','assigned')->count();
        $completed = Task::where('status','completed')->count();
        $inprogress = Task::where('status', 'inprogress')->count();
        return response()->json(['assigned'=>$assigned, 'completed'=>$completed, 'inprogress'=>$inprogress]);
    }

    public function task(){
    //    foreach($users as $user){
        //    $users = User::find(6)->tasks1()->where('title','vmock')->get();
        //    return $users;
        
        
    }
}


     

