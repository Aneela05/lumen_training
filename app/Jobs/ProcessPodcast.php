<?php

namespace App\Jobs;

// p
// use App\Mail\Sendtask;

use Illuminate\Bus\Queueable;
use Throwable;
use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessPodcast implements ShouldQueue
{
    use  InteractsWithQueue,Queueable,SerializesModels;
    public  $email, $mailable;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $mailable)
    {
        $this->email = $email;
        $this->mailable = $mailable;
       
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function failed(Throwable $exception){
        
        // dd($exception);
        dump($exception);
     }
    public function handle()
    
    {
        
        Mail::to($this->email)->send($this->mailable);
    }
}
