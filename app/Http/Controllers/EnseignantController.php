<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Models\Enseignant;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EnseignantController extends Controller
{
    public function index()
    {
        $search = request('q');

        $enseignantsQuery = Enseignant::query()
            ->select(['id', 'nom', 'prenom', 'matricule'])
            ->withCount('cours');

        $enseignantsQuery->addSelect(['authorisations_count' => DB::table('cours_enseignants')
            ->selectRaw('count(*)')
            ->whereColumn('cours_enseignants.enseignant_id', 'enseignants.id')
            ->where('cours_enseignants.active', true)
        ]);

        $enseignantsQuery->addSelect(['last_course_date' => Cours::query()
            ->selectRaw('max(cours.date)')
            ->join('cours_enseignants', 'cours.id', '=', 'cours_enseignants.cours_id')
            ->whereColumn('cours_enseignants.enseignant_id', 'enseignants.id')
        ]);

        if (!empty($search)) {
            $driver = config('database.default');
            $like = $driver === 'pgsql' ? 'ilike' : 'like';
            $enseignantsQuery->where(function ($q) use ($search, $like) {
                $q->where('nom', $like, "%{$search}%")
                    ->orWhere('prenom', $like, "%{$search}%")
                    ->orWhere('matricule', $like, "%{$search}%");
            });
        }

        $enseignants = $enseignantsQuery->orderBy('nom')->paginate()->appends(['q' => $search]);

        return Inertia::render('Teachers', [
            'enseignants' => $enseignants,
            'filters' => ['q' => $search],
        ]);
    }
}
