<?php

namespace App\Console\Commands;

use App\Jobs\SyncHyperplanningCourseJob;
use Illuminate\Console\Command;

class RunSyncHyperplanningCourse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyperplanning:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les cours de chaques salles avec serrures';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SyncHyperplanningCourseJob::dispatch();
        $this->info("Job dispatched");
    }
}
