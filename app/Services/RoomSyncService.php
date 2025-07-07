<?php

namespace App\Services;

use App\Models\Salle;
use App\Services\HyperplanningRestService;
use Illuminate\Support\Facades\Log;

/**
 * Service métier chargé de synchroniser les salles disposant
 * de serrures électroniques Dorma (famille 62) entre l'API Hyperplanning
 * et la base de données locale.
 *
 * Ce service est typiquement utilisé dans un Job planifié mensuellement.
 * Il permet de maintenir à jour les informations des salles :
 * - libellé
 * - identifiant Hyperplanning (cle)
 * - identifiants des serrures Dorma
 */
class RoomSyncService
{
    /**
     * Instance du service Hyperplanning REST (injection automatique).
     *
     * @param HyperplanningRestService $hp
     */
    public function __construct(
        protected HyperplanningRestService $hp
    ) {}

    /**
     * Lance le processus de synchronisation des salles avec serrure Dorma.
     *
     * Étapes :
     * 1. Récupère toutes les salles depuis l'API Hyperplanning
     * 2. Pour chaque salle, récupère les éventuelles serrures Dorma associées
     * 3. Enregistre ou met à jour la salle si :
     *    - au moins une serrure est trouvée
     *    - ou si la salle n'existe pas encore en base
     * 4. Journalise chaque action dans les logs
     *
     * @return array Résumé des salles synchronisées, indexées par leur `cle` avec les ID de serrure associés
     */
    public function sync(): array
    {
        // Appel à l’API pour récupérer toutes les salles disponibles
        $salles = $this->hp->getSalles();
        $dormaIds = [];

        foreach ($salles as $salle) {
            // Récupère les identifiants de serrures pour cette salle (famille 62 = Dorma)
            $id = $this->hp->getDormaIdsForOneRoom($salle["cle"]);

            // Ne traite que les salles avec serrure(s) ou absentes en base
            if (!empty($id)) {
                $dormaIds[$salle["cle"]] = $id;

                // Log d'information : salle synchronisée
                Log::info("🔐 Salle synchronisée", [
                    'hp_id' => $salle["cle"],
                    'nom'   => $salle["nom"],
                    'dorma' => $id,
                ]);

                // Enregistre ou met à jour la salle en base de données
                $existingSalle = Salle::where('hp_id', $salle["cle"])->first();

                if (!$existingSalle || $existingSalle->libelle !== $salle["nom"] || $existingSalle->dorma !== $id) {
                    Salle::updateOrCreate(
                        ['hp_id' => $salle["cle"]],
                        [
                            'libelle' => $salle["nom"],
                            'dorma'   => $id,
                        ]
                    );
                }
            }
        }

        // Log global de fin de traitement
        Log::info('✅ Synchronisation des serrures terminée', [
            'salles' => array_keys($dormaIds)
        ]);

        return $dormaIds;
    }
}
