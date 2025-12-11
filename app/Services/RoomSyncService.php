<?php

namespace App\Services;

use App\Models\Salle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RoomSyncService
{
    /**
     * Constructeur du service.
     */
    public function __construct(
        protected HyperplanningRestService $hp,
        protected MatrixService $ms
    ) {}

    /**
     * Synchronise les salles entre Hyperplanning et la base de données locale.
     */
    public function sync(): array
    {
        $startTime = microtime(true);
        $stats = [
            'total_salles' => 0,
            'salles_avec_serrures' => 0,
            'salles_mises_a_jour' => 0,
            'erreurs' => []
        ];

        try {
            Log::info('🚀 Démarrage de la synchronisation des salles');
            DB::beginTransaction();
            
            // 1. Récupérer les salles depuis Hyperplanning
            Log::info('🔍 Récupération des salles depuis Hyperplanning...');
            $salles = $this->hp->getSalles();
            $stats['total_salles'] = count($salles);
            
            if (empty($salles)) {
                Log::warning('Aucune salle trouvée dans Hyperplanning');
                return $stats;
            }
            Log::info("✅ " . count($salles) . " salles récupérées");

            // 2. Filtrer les salles avec serrures
            Log::info('🔍 Filtrage des salles avec serrures...');
            $sallesAvecSerrures = $this->getSallesAvecSerrures($salles);
            $stats['salles_avec_serrures'] = count($sallesAvecSerrures);
            Log::info("🔑 " . count($sallesAvecSerrures) . " salles avec serrures trouvées");

            // 3. Synchroniser les salles
            $resultats = $this->synchroniserSalles($sallesAvecSerrures);
            $stats = array_merge($stats, $resultats);

            DB::commit();
            
            $duration = round(microtime(true) - $startTime, 2);
            Log::info("✅ Synchronisation terminée avec succès en {$duration}s", $stats);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = '❌ Erreur lors de la synchronisation des salles: ' . $e->getMessage();
            Log::error($errorMsg, [
                'exception' => get_class($e),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $stats['erreurs'][] = $errorMsg;
            throw $e;
        }
        
        return $stats;
    }

    /**
     * Filtre les salles qui ont des serrures.
     */
    private function getSallesAvecSerrures(array $salles): array
    {
        return array_filter($salles, function($salle) {
            $rubriques = $this->hp->getDormaIdsForOneRoom($salle["cle"]);
            return !empty($rubriques);
        });
    }

    /**
     * Récupère les identifiants des serrures pour une salle.
     */
    private function getSerruresPourSalle(array $salle): array
    {
        $rubriques = $this->hp->getDormaIdsForOneRoom($salle["cle"]);
        return array_filter(array_map('trim', $rubriques), 'is_numeric');
    }

    /**
     * Récupère les noms des portes depuis le service Matrix.
     */
    private function getNomsPortes(array $idsSerrures): array
    {
        $nomsPortes = [];
        foreach ($idsSerrures as $idSerrures) {
            try {
                $nomPorte = $this->ms->getDoorName($idSerrures);
                if ($nomPorte) {
                    $nomsPortes[] = $nomPorte;
                }
            } catch (\Exception $e) {
                Log::warning("Impossible de récupérer le nom de la porte $idSerrures", [
                    'error' => $e->getMessage()
                ]);
            }
        }
        return $nomsPortes;
    }

    /**
     * Synchronise une liste de salles avec la base de données.
     */
    private function synchroniserSalles(array $salles): array
    {
        $stats = [
            'salles_traitees' => 0,
            'salles_mises_a_jour' => 0,
            'erreurs' => []
        ];
        
        $totalSalles = count($salles);
        $startTime = microtime(true);
        
        foreach ($salles as $index => $salle) {
            $stats['salles_traitees']++;
            
            // Log de progression toutes les 10 salles
            if (($index + 1) % 10 === 0 || ($index + 1) === $totalSalles) {
                $progress = round(($index + 1) / $totalSalles * 100);
                $elapsed = round(microtime(true) - $startTime, 2);
                Log::info("🔄 Progression: {$progress}% ({$index}/{$totalSalles}) - {$elapsed}s écoulés");
            }
            
            try {
                if ($this->synchroniserSalle($salle)) {
                    $stats['salles_mises_a_jour']++;
                }
            } catch (\Exception $e) {
                $errorMsg = "Erreur lors de la synchronisation de la salle {$salle['cle']} ({$salle['nom']}): " . $e->getMessage();
                Log::error($errorMsg);
                $stats['erreurs'][] = $errorMsg;
            }
        }
        
        return $stats;
    }

    /**
     * Synchronise une salle avec la base de données.
     */
    private function synchroniserSalle(array $salle): bool
    {
        $idsSerrures = $this->getSerruresPourSalle($salle);
        
        if (empty($idsSerrures)) {
            Log::debug("Aucune serrure trouvée pour la salle", [
                'salle_id' => $salle['cle'],
                'nom' => $salle['nom']
            ]);
            return false;
        }

        $nomsPortes = $this->getNomsPortes($idsSerrures);
        
        $donneesSalle = [
            'libelle_hp' => $salle['nom'],
            'dorma' => $idsSerrures,
            'libelles_matrix' => $nomsPortes
        ];

        // Vérifier si une mise à jour est nécessaire
        $salleExiste = Salle::where('hp_id', $salle['cle'])->first();
        
        if ($this->doitMettreAJour($salleExiste, $donneesSalle)) {
            $operation = $salleExiste ? 'mise à jour' : 'création';
            
            try {
                Salle::updateOrCreate(
                    ['hp_id' => $salle['cle']],
                    $donneesSalle
                );
                
                Log::info("✅ Salle {$operation} avec succès", [
                    'operation' => $operation,
                    'hp_id' => $salle['cle'],
                    'nom' => $salle['nom'],
                    'dorma' => $idsSerrures,
                    'libelles_matrix' => $nomsPortes
                ]);
                
                return true;
            } catch (\Exception $e) {
                Log::error("❌ Échec de l'{$operation} de la salle", [
                    'hp_id' => $salle['cle'],
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
        
        Log::debug("⏭️ Salle non modifiée", [
            'hp_id' => $salle['cle'],
            'nom' => $salle['nom']
        ]);
        
        return false;
    }

    public function syncMissingMatrixLabels(int $batchSize = 5, int $delayBetweenBatches = 2, int $maxCallsBeforePause = 80, int $pauseDuration = 60): array
{
    $stats = [
        'total_salles_traitees' => 0,
        'salles_mises_a_jour' => 0,
        'erreurs' => [],
        'appels_effectues' => 0,
        'pauses_effectuees' => 0
    ];

    try {
        // Récupérer les salles sans libellé Matrix
        $sallesSansLibelle = Salle::where(function ($query) {
            $query->whereNull('libelles_matrix')
                ->orWhereJsonLength('libelles_matrix', 0);
        })->get();

        $stats['total_salles_a_traiter'] = $sallesSansLibelle->count();

        if ($sallesSansLibelle->isEmpty()) {
            Log::info('✅ Aucune salle sans libellé Matrix trouvée');
            return $stats;
        }

        Log::info("🔍 Début de la synchronisation pour {$stats['total_salles_a_traiter']} salles");

        foreach ($sallesSansLibelle->chunk($batchSize) as $index => $batch) {
            $batchStartTime = microtime(true);
            $batchNumber = $index + 1;
            $totalBatches = ceil($sallesSansLibelle->count() / $batchSize);
            
            Log::info("📦 Traitement du lot {$batchNumber}/{$totalBatches}");

            foreach ($batch as $salle) {
                try {
                    // Vérifier si on approche de la limite d'appels
                    if ($stats['appels_effectues'] >= $maxCallsBeforePause) {
                        $stats['pauses_effectuees']++;
                        $waitTime = $pauseDuration + 5; // 5 secondes de marge
                        Log::warning("⏳ Limite d'appels atteinte. Pause de {$waitTime} secondes...");
                        sleep($waitTime);
                        $stats['appels_effectues'] = 0; // Réinitialiser le compteur
                    }

                    $idsSerrures = $salle->dorma ?? [];

                    if (empty($idsSerrures)) {
                        Log::debug("Aucune serrure pour la salle ID: {$salle->id}");
                        continue;
                    }

                    // Compter un appel API pour chaque serrure
                    $stats['appels_effectues'] += count($idsSerrures);

                    Log::debug("Traitement de la salle ID: {$salle->id}, Serrures: " . json_encode($idsSerrures));

                    $nomsPortes = $this->getNomsPortes($idsSerrures);

                    if (!empty($nomsPortes)) {
                        $salle->libelles_matrix = $nomsPortes;
                        $salle->save();
                        $stats['salles_mises_a_jour']++;
                        Log::debug("✅ Mise à jour de la salle ID: {$salle->id}");
                    }

                    $stats['total_salles_traitees']++;

                    // Petit délai entre chaque salle (200ms)
                    usleep(200000);

                } catch (\Exception $e) {
                    $errorMsg = "Erreur lors du traitement de la salle ID: {$salle->id} - " . $e->getMessage();
                    Log::error($errorMsg);
                    $stats['erreurs'][] = $errorMsg;
                    
                    // En cas d'erreur, on fait une pause plus longue
                    sleep(5);
                }
            }

            $batchTime = round(microtime(true) - $batchStartTime, 2);
            $avgTimePerBatch = $batchTime / $batchSize;
            $estimatedRemaining = ($totalBatches - $batchNumber) * $avgTimePerBatch / 60;
            
            Log::info(sprintf(
                "⏱️  Lot %d/%d terminé en %.2fs | Progression: %d%% | Temps restant estimé: %.1f min",
                $batchNumber,
                $totalBatches,
                $batchTime,
                round(($batchNumber / $totalBatches) * 100),
                $estimatedRemaining
            ));

            // Délai entre les lots (sauf après le dernier lot)
            if ($batchNumber < $totalBatches) {
                Log::info("⏳ Pause de {$delayBetweenBatches}s avant le prochain lot...");
                sleep($delayBetweenBatches);
            }
        }

        Log::info('✅ Synchronisation terminée', $stats);
        return $stats;

    } catch (\Exception $e) {
        $errorMsg = 'Erreur lors de la synchronisation: ' . $e->getMessage();
        Log::error($errorMsg);
        $stats['erreurs'][] = $errorMsg;
        return $stats;
    }
}

    /**
     * Détermine si une salle doit être mise à jour.
     */
    private function doitMettreAJour(?Salle $salle, array $nouvellesDonnees): bool
    {
        if (!$salle) {
            return true;
        }

        return $salle->libelle_hp !== $nouvellesDonnees['libelle_hp'] ||
               $salle->dorma != $nouvellesDonnees['dorma'] ||
               $salle->libelles_matrix != $nouvellesDonnees['libelles_matrix'];
    }
}