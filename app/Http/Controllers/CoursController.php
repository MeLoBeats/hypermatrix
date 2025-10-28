<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use Inertia\Inertia;
use Illuminate\Http\Request;

class CoursController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');
        $driver = config('database.default');
        $like = $driver === 'pgsql' ? 'ilike' : 'like';

        $coursQuery = Cours::query()
            ->with([
                'salle:id,libelle,dorma',
                'enseignants:id,nom,prenom,matricule',
            ])
            ->select(['id', 'hp_id', 'salle_id', 'date'])
            ->orderByDesc('date');

        if (!empty($search)) {
            $coursQuery->whereHas('salle', function ($q) use ($search, $like) {
                    $q->where('libelle', $like, "%{$search}%");
                })
                ->orWhereHas('enseignants', function ($q) use ($search, $like) {
                    $q->where('nom', $like, "%{$search}%")
                      ->orWhere('prenom', $like, "%{$search}%");
                });
        }

        $cours = $coursQuery->paginate()->appends(['q' => $search]);

        return Inertia::render('Planning', [
            'cours' => $cours,
            'filters' => ['q' => $search],
        ]);
    }
}
