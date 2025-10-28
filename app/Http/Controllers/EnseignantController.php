<?php

namespace App\Http\Controllers;

use App\Models\Enseignant;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Http\Request;

class EnseignantController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');
        $driver = config('database.default');
        $like = $driver === 'pgsql' ? 'ilike' : 'like';

        $enseignantsQuery = Enseignant::query()
            ->select(['id', 'nom', 'prenom', 'matricule'])
            ->withCount('cours');

        $enseignantsQuery->addSelect([
            'authorisations_count' => DB::table('cours_enseignants')
                ->selectRaw('count(*)')
                ->whereColumn('cours_enseignants.enseignant_id', 'enseignants.id')
                ->where('cours_enseignants.active', true),
            
            'last_course_date' => \App\Models\Cours::query()
                ->selectRaw('max(cours.date)')
                ->join('cours_enseignants', 'cours.id', '=', 'cours_enseignants.cours_id')
                ->whereColumn('cours_enseignants.enseignant_id', 'enseignants.id')
        ]);

        if (!empty($search)) {
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
