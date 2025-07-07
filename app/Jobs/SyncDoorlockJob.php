<?php

namespace App\Jobs;

use App\Services\RoomSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job Laravel pour déclencher la synchronisation des serrures Dorma.
 * Ce job délègue le traitement à un service métier dédié.
 */
class SyncDoorlockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Exécute le job de synchronisation.
     *
     * @param RoomSyncService $syncService
     * @return void
     */
    public function handle(RoomSyncService $syncService): void
    {
        $syncService->sync();
    }
}
