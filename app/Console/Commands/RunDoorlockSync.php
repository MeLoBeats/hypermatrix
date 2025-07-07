<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncDoorlockJob;
use App\Services\HyperplanningRestService;

class RunDoorlockSync extends Command
{
    protected $signature = 'doorlock:sync';

    protected $description = 'Synchronise les serrures des salles (test manuel)';

    public function handle(HyperplanningRestService $hp)
    {
        SyncDoorlockJob::dispatchSync($hp); // ✅ Exécution immédiate
        $this->info('Job exécuté.');
    }
}
