<?php

namespace App\Http\Controllers;

use App\Jobs\SyncHyperplanningCourseJob;
use App\Models\Cours;
use Inertia\Inertia;

class CoursController extends Controller
{
    public function index()
    {
        // Simple search filter from query string.
        $search = request('q');

        $coursQuery = Cours::query()
            ->with([
                'salle:id,libelle_hp,dorma',
                'enseignants:id,nom,prenom,matricule',
            ])
            ->select(['id', 'hp_id', 'salle_id', 'date'])
            ->orderByDesc('date');

        if (!empty($search)) {
            $driver = config('database.default');
            $like = $driver === 'pgsql' ? 'ilike' : 'like';

            // Group OR filters to avoid leaking into future conditions.
            $coursQuery->where(function ($q) use ($search, $like) {
                $q->whereHas('salle', function ($sub) use ($search, $like) {
                    $sub->where('libelle_hp', $like, "%{$search}%");
                })
                ->orWhereHas('enseignants', function ($sub) use ($search, $like) {
                    $sub->where('nom', $like, "%{$search}%")
                        ->orWhere('prenom', $like, "%{$search}%");
                });
            });
        }

        // Keep the current filters across pagination links.
        $cours = $coursQuery->paginate()->withQueryString();

        return Inertia::render('Planning', [
            'cours' => $cours,
            'filters' => [
                'q' => $search,
            ],
        ]);
    }

    public function sync()
    {
        SyncHyperplanningCourseJob::dispatch();

        return redirect()->back()->with('status', 'Sync queued');
    }
}
