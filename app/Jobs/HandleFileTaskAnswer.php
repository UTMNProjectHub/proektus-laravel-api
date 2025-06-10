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

    public int $userId;
    public int $projectId;
    public string $status;
    public ?string $message = null;
    public ?string $error = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        int     $userId,
        int     $projectId,
        string  $status,
        ?string $message = null,
        ?string $error = null
    )
    {
        $this->userId = $userId;
        $this->projectId = $projectId;
        $this->status = $status;
        $this->message = $message;
        $this->error = $error;
    }

    public function handle(): void
    {
        Log::info($this->userId);

        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new FileProcessed($this->status === 'success' ? $this->message : $this->error, $this->status, $this->projectId, $this->userId));
            $this->delete();
        } else {
            Log::error("User with ID {$this->userId} not found.");
            $this->fail('User not found');
        }
    }
}
