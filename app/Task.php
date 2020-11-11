<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\Mailable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Tymon\JWTAuth\Contracts\JWTSubject;

class Task extends Model implements AuthenticatableContract, AuthorizableContract,JWTSubject
{
    use Authenticatable, Authorizable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title','description','duedate','assignee', 'status','user_id'
    ];
    //defining the relationship on the the task model from the user.
    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function assigneeUser(){
        return $this->belongsTo(User::class, 'assignee');
    }

    
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    protected $hidden = [
        
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }
    
    protected $table = "tasks";
    

}


