<?php

namespace App\Http\Controllers;

use App\Models\Enseignant;
use App\Models\Salle;
use Inertia\Inertia;

class SalleController extends Controller
{
    public function index()
    {
        $search = request('q');

        $sallesQuery = Salle::query()
            ->withCount('cours')
            ->select(['id', 'hp_id', 'libelle_hp as libelle', 'dorma', 'libelles_matrix']);

        $sallesQuery->addSelect(['enseignants_count' => Enseignant::query()
            ->selectRaw('count(distinct cours_enseignants.enseignant_id)')
            ->join('cours_enseignants', 'enseignants.id', '=', 'cours_enseignants.enseignant_id')
            ->join('cours', 'cours.id', '=', 'cours_enseignants.cours_id')
            ->whereColumn('cours.salle_id', 'salles.id')
        ]);

        if (!empty($search)) {
            $driver = config('database.default');
            if ($driver === 'pgsql') {
                $sallesQuery->where('libelle', 'ilike', "%{$search}%");
            } else {
                $sallesQuery->where('libelle', 'like', "%{$search}%");
            }
        }

        $salles = $sallesQuery->paginate()->appends(['q' => $search]);

        $salles->getCollection()->transform(function ($salle) {
            $salle->has_hp_match = !empty($salle->hp_id);

            if (is_string($salle->libelles_matrix)) {
                $salle->libelles_matrix = json_decode($salle->libelles_matrix, true) ?? [];
            }

            return $salle;
        });

        return Inertia::render('Doors', [
            'salles' => $salles,
            'filters' => [
                'q' => $search,
            ],
        ]);
    }
}
