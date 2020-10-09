<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Signupverify extends Mailable
{
    use Queueable, SerializesModels;

    public $token, $name;

    public function __construct($user)
    {
        $this->token = $user->token;
        $this->name  = $user->name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.signup',['name' => $this->name, 'token' => $this->token,]);
    }
}