<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessProjectFile implements ShouldQueue
{
    use Queueable;

    protected $project_id;
    protected $user_id;
    protected $object_keys;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int   $project_id,
        int   $user_id,
        array $object_keys
    )
    {
        $this->project_id = $project_id;
        $this->user_id = $user_id;
        $this->object_keys = $object_keys;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

    }
}
