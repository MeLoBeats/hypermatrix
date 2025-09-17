<?php

namespace App\Jobs;

use App\Services\SyncMatrixPersonService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncMatrixPersonJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SyncMatrixPersonService $matrixService): void
    {
        $matrixService->sync();
    }
}
