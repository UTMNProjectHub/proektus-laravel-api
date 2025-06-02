<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\FileProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleFileTaskAnswer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $projectId;
    public $status;
    public $message = null;
    public $error = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $user_id,
        $project_id,
        $status,
        $message = null,
        $error = null
    ) {
        $this->userId    = $user_id;
        $this->projectId = $project_id;
        $this->status    = $status;
        $this->message   = $message;
        $this->error     = $error;
    }

    public function handle(): void
    {
        Log::info($this->userId);

//        $user = User::find($this->userId);
//        if ($user) {
//            $user->notify(new FileProcessed($this->projectId, $this->status, $this->message, $this->error));
//        }
    }

//    public $a, $b, $c;
//
//    /**
//     * Create a new job instance.
//     *
//     * @return void
//     */
//    public function __construct ($a, $b, $c) {
//        $this->a = $a;
//        $this->b = $b;
//        $this->c = $c;
//    }
//
//    public function handle () {
//        Log::info('TEST: ' . $this->a . ' '. $this->b . ' ' . $this->c);
//    }
}
