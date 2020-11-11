<?php

namespace App\Mail;

use App\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Sendtask extends Mailable
{
    use Queueable, SerializesModels;

    public   $task;

    public function __construct($task)
    {
        
        $this->task = $task;
        // $this->title = $task->title;
        // $this->description = $task->description;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->subject('Assigned a Task for you')->view('emails.sendtask',[ 'title' => $this->title, 'description'=>$this->description]);
        return $this->subject('Task Assigned')->view('emails.sendtask',['details'=> $this->task]);
        // return $this->subject('task assigned');
    }
}