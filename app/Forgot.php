<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\Mailable;

use Tymon\JWTAuth\Contracts\JWTSubject;

class Forgot extends Model implements AuthenticatableContract, AuthorizableContract,JWTSubject
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    protected $hidden = [
        'token','created_at','updated_at'
    ];
  
    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }
    
    protected $table= 'passwords';
    

}


