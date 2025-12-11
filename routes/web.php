<?php

use App\Services\MatrixService;
use App\Services\SyncMatrixPersonService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $driver = config('database.default');

    $sallesCount = \App\Models\Salle::count();

    // total serrures Dorma (json array length sum)
    if ($driver === 'pgsql') {
        $totalSerrures = \App\Models\Salle::selectRaw('COALESCE(sum(json_array_length(dorma)), 0) as total')->value('total');
    } else {
        // Fallback: addition via PHP
        $totalSerrures = \App\Models\Salle::all()->reduce(function ($acc, $salle) {
            $arr = is_array($salle->dorma) ? $salle->dorma : [];
            return $acc + count($arr);
        }, 0);
    }

    $enseignantsCount = \App\Models\Enseignant::count();

    $today = now()->toDateString();
    $coursToday = \App\Models\Cours::whereDate('date', $today)->count();

    return Inertia::render('Home', [
        'dashboard' => [
            'enseignants' => $enseignantsCount,
            'salles' => $sallesCount,
            'serrures' => (int) $totalSerrures,
            'cours_today' => $coursToday,
            // Optionally surface placeholder sync info
            'last_sync_salles' => null,
            'last_sync_cours' => null,
        ],
    ]);
});

Route::get('/salles', function () {
    $search = request('q');

    $sallesQuery = \App\Models\Salle::query()
        ->withCount('cours')
        ->select(['id', 'hp_id', 'libelle_hp as libelle', 'dorma', 'libelles_matrix']);

    // Add enseignants_count via subquery on pivot cours_enseignants
    $sallesQuery->addSelect(['enseignants_count' => \App\Models\Enseignant::query()
        ->selectRaw('count(distinct cours_enseignants.enseignant_id)')
        ->join('cours_enseignants', 'enseignants.id', '=', 'cours_enseignants.enseignant_id')
        ->join('cours', 'cours.id', '=', 'cours_enseignants.cours_id')
        ->whereColumn('cours.salle_id', 'salles.id')
    ]);

    // Search by salle name
    if (!empty($search)) {
        $driver = config('database.default');
        if ($driver === 'pgsql') {
            $sallesQuery->where('libelle', 'ilike', "%{$search}%");
        } else {
            $sallesQuery->where('libelle', 'like', "%{$search}%");
        }
    }

    $salles = $sallesQuery->paginate()->appends(['q' => $search]);

    // Ajouter la correspondance HP (has_hp_match) à chaque salle
    $salles->getCollection()->transform(function ($salle) {
        // Une salle a une correspondance HP si elle a un hp_id non nul
        $salle->has_hp_match = !empty($salle->hp_id);
        
        // Si libelles_matrix est une chaîne, la convertir en tableau
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
});

Route::get('/cours', function () {
    $search = request('q');

    $coursQuery = \App\Models\Cours::query()
        ->with([
            'salle:id,libelle_hp,dorma',
            'enseignants:id,nom,prenom,matricule',
        ])
        ->select(['id', 'hp_id', 'salle_id', 'date'])
        ->orderByDesc('date');

    if (!empty($search)) {
        $driver = config('database.default');
        $like = $driver === 'pgsql' ? 'ilike' : 'like';

        $coursQuery
            ->whereHas('salle', function ($q) use ($search, $like) {
                $q->where('libelle_hp', $like, "%{$search}%");
            })
            ->orWhereHas('enseignants', function ($q) use ($search, $like) {
                $q->where('nom', $like, "%{$search}%")
                  ->orWhere('prenom', $like, "%{$search}%");
            });
    }

    $cours = $coursQuery->paginate()->appends(['q' => $search]);

    return Inertia::render('Planning', [
        'cours' => $cours,
        'filters' => [
            'q' => $search,
        ],
    ]);
});

Route::get('/logs', function () {
    return Inertia::render('Logs');
});

Route::get('/enseignants', function () {
    $search = request('q');

    $enseignantsQuery = \App\Models\Enseignant::query()
        ->select(['id', 'nom', 'prenom', 'matricule'])
        ->withCount('cours');

    // authorisations_count = number of pivot rows with active = true
    $enseignantsQuery->addSelect(['authorisations_count' => DB::table('cours_enseignants')
        ->selectRaw('count(*)')
        ->whereColumn('cours_enseignants.enseignant_id', 'enseignants.id')
        ->where('cours_enseignants.active', true)
    ]);

    // last_course_date via subquery on cours joined through pivot
    $enseignantsQuery->addSelect(['last_course_date' => \App\Models\Cours::query()
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
        'filters' => [ 'q' => $search ],
    ]);
});

Route::get('/test', function (MatrixService $ms, SyncMatrixPersonService $sms) {
    $allDataToSync = $sms->sync();
    foreach ($allDataToSync as $data) {
        $from = Carbon::parse($data->date)->subWeek();
        $until = Carbon::parse($data->date);

        $newAccess = $ms->createAccessPermissionByMatricule($data->id, $data->salle->dorma, $from, $until);
        dump($newAccess, $data->enseignants->pluck('matricule'));
    }
});

Route::get('/teste/{id}', function (string $id, MatrixService $ms) {
    return $ms->createAccessPermissionByMatricule($id, "7181", Carbon::now(), Carbon::now()->addMonth());
});

Route::get('/doors/{id}', function(int $id, MatrixService $ms) {
    return $ms->xmlToArrayFromString($ms->getDoors($id))["Name"];
});