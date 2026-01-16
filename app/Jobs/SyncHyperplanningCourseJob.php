<?php

namespace App\Jobs;

use App\Models\Salle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job principal : planifie un job par salle pour synchroniser les cours Hyperplanning.
 */
class SyncHyperplanningCourseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function tags(): array
    {
        return ['hyperplanning', 'cours', 'sync'];
    }

    public function handle(): void
    {
        $salles = Salle::all();

        foreach ($salles as $salle) {
            Log::info("📦 Dispatch job pour la salle {$salle->libelle}");
            SyncSalleCoursesJob::dispatch($salle->id);
        }

        Log::info("✅ Tous les jobs de synchronisation de salles ont été dispatchés.");
    }
}
