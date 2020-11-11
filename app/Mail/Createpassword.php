<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Createpassword extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    public function __construct($user)
    {
        $this->token = $user->token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.create', ['token' => $this->token]);
    }
}