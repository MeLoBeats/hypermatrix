<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use App\Models\Enseignant;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SalleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');

        $sallesQuery = Salle::query()
            ->withCount('cours')
            ->select(['id', 'hp_id', 'libelle', 'dorma']);

        $sallesQuery->addSelect(['enseignants_count' => Enseignant::query()
            ->selectRaw('count(distinct cours_enseignants.enseignant_id)')
            ->join('cours_enseignants', 'enseignants.id', '=', 'cours_enseignants.enseignant_id')
            ->join('cours', 'cours.id', '=', 'cours_enseignants.cours_id')
            ->whereColumn('cours.salle_id', 'salles.id')
        ]);

        if (!empty($search)) {
            $driver = config('database.default');
            $sallesQuery->where('libelle', $driver === 'pgsql' ? 'ilike' : 'like', "%{$search}%");
        }

        $salles = $sallesQuery->paginate()->appends(['q' => $search]);

        return Inertia::render('Doors', [
            'salles' => $salles,
            'filters' => ['q' => $search],
        ]);
    }
}
