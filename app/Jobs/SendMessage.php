<?php

namespace App\Jobs;

use App\Events\NewMessageEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendMessage implements ShouldQueue
{
    use Queueable;

    protected $mess;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->mess = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Sending messages');
        event(new NewMessageEvent($this->mess));
    }
}
