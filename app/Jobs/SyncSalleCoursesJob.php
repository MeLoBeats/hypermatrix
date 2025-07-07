<?php

namespace App\Jobs;

use App\Models\Cours;
use App\Models\Enseignant;
use App\Models\Salle;
use App\Services\HyperplanningRestService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job responsable de la synchronisation des cours Hyperplanning pour une salle donnée.
 */
class SyncSalleCoursesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $salleId) {}

    public function handle(HyperplanningRestService $hp): void
    {
        $salle = Salle::find($this->salleId);

        if (!$salle) {
            Log::warning("❌ Salle introuvable ID={$this->salleId}");
            return;
        }

        $dateDebut = Carbon::create(2025, 2, 1);
        $dateFin = $dateDebut->copy()->addMonth();

        Log::info("📘 Synchronisation de la salle : {$salle->libelle}");

        $courseIds = $hp->getCoursesByRoomBetweenDates(
            $salle->hp_id,
            $dateDebut->format('Y-m-d'),
            $dateFin->format('Y-m-d')
        );

        if (empty($courseIds)) {
            Log::info("❌ Aucun cours trouvé pour la salle", ['salle_id' => $salle->id]);
            return;
        }

        $rawCourseData = $hp->getCoursesData($courseIds);
        $allSessions = collect($rawCourseData)->flatten(1);

        $filteredCourses = $allSessions->filter(function ($cours) use ($dateDebut, $dateFin) {
            if (!isset($cours['jour_heure_debut'])) return false;
            $debut = Carbon::parse($cours['jour_heure_debut']);
            return $debut->between($dateDebut, $dateFin);
        })->values();

        if ($filteredCourses->isEmpty()) return;

        try {
            DB::beginTransaction();

            // 🧠 Étape 1 : récupérer tous les ID Hyperplanning enseignants
            $ensHpIds = $filteredCourses
                ->pluck('enseignants')
                ->flatten()
                ->unique()
                ->filter()
                ->values();

            // 🧠 Étape 2 : vérifier ceux déjà en base
            $existingHpIds = Enseignant::whereIn('hp_id', $ensHpIds)->pluck('hp_id');
            $missingHpIds = $ensHpIds->diff($existingHpIds);

            $enseignantsInDb = [];

            // 🧠 Étape 3 : ajouter ceux manquants en base
            if ($missingHpIds->isNotEmpty()) {
                $apiData = $hp->getEnseignantsData($missingHpIds->toArray());

                foreach ($apiData as $data) {
                    $enseignantsInDb[$data['code']] = Enseignant::updateOrCreate(
                        ['matricule' => $data['code']],
                        [
                            'nom'       => $data['nom'] ?? '',
                            'prenom'    => $data['prenom'] ?? '',
                            'matricule' => $data['code'],
                            'hp_id'     => $data['cle'] ?? null,
                        ]
                    );
                }
            }

            // 🧠 Étape 4 : charger tous les enseignants (existants + ajoutés)
            $enseignantsTotal = Enseignant::whereIn('hp_id', $ensHpIds)->get();
            $enseignantsMap = $enseignantsTotal->keyBy('hp_id');

            foreach ($filteredCourses as $cours) {
                $coursId = $cours['cle'];
                $date = Carbon::parse($cours['jour_heure_debut'])->format('Y-m-d');

                $exists = Cours::where('hp_id', $coursId)
                    ->where('salle_id', $salle->id)
                    ->where('date', $date)
                    ->exists();

                if ($exists) {
                    Log::info("🔁 Cours déjà enregistré", [
                        'cours_id' => $coursId,
                        'salle_id' => $salle->id,
                        'date'     => $date,
                    ]);
                    continue;
                }

                $coursModel = Cours::create([
                    'hp_id'    => $coursId,
                    'salle_id' => $salle->id,
                    'date'     => $date,
                ]);

                foreach ($cours['enseignants'] ?? [] as $ensHpId) {
                    if (isset($enseignantsMap[$ensHpId])) {
                        $coursModel->enseignants()->syncWithoutDetaching([$enseignantsMap[$ensHpId]->id]);
                    }
                }

                Log::info("📍 Nouveau cours enregistré", [
                    'cours_id' => $coursId,
                    'salle_id' => $salle->id,
                    'date'     => $date,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("❌ Erreur pendant la synchronisation", [
                'salle_id'  => $salle->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
