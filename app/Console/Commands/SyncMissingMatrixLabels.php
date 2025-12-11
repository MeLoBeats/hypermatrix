<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RoomSyncService;

class SyncMissingMatrixLabels extends Command
{
    protected $signature = 'sync:missing-matrix-labels 
        {--batch=5 : Nombre de salles par lot} 
        {--delay=2 : Délai en secondes entre les lots}
        {--max-calls=80 : Nombre maximum d\'appels avant une pause}
        {--pause=65 : Durée de la pause en secondes}';    
    protected $description = 'Synchronise les libellés Matrix manquants pour les salles';

    public function handle(RoomSyncService $roomSyncService)
{
        $this->info('🚀 Démarrage de la synchronisation des libellés Matrix manquants...');
        
        $result = $roomSyncService->syncMissingMatrixLabels(
            $this->option('batch'),
            $this->option('delay'),
            $this->option('max-calls'),
            $this->option('pause')
        );

        $this->info('📊 Résumé:');
        $this->line("- Salles traitées: {$result['total_salles_traitees']}");
        $this->line("- Salles mises à jour: {$result['salles_mises_a_jour']}");
        
        if (!empty($result['erreurs'])) {
            $this->error('❌ Des erreurs sont survenues:');
            foreach ($result['erreurs'] as $error) {
                $this->error("- $error");
            }
            return 1;
        }

        $this->info('✅ Synchronisation terminée avec succès !');
        return 0;
    }
}