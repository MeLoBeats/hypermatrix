<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncDoorlockJob;

class RunDoorlockSync extends Command
{
    protected $signature = 'doorlock:sync';
    protected $description = 'Synchronise les serrures des salles avec le système Dormakaba';

    public function handle()
    {
        $this->info('Démarrage de la synchronisation des serrures...');
        
        try {
            // Utilisation de dispatchSync pour une exécution immédiate (synchrone)
            SyncDoorlockJob::dispatchSync();
            $this->info('✅ Synchronisation terminée avec succès.');
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la synchronisation : ' . $e->getMessage());
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            return 1; // Code d'erreur
        }
        
        return 0; // Succès
    }
}
