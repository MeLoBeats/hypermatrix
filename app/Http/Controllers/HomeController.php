<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Models\Enseignant;
use App\Models\Salle;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $driver = config('database.default');

        $sallesCount = Salle::count();

        if ($driver === 'pgsql') {
            $totalSerrures = Salle::selectRaw('COALESCE(sum(json_array_length(dorma)), 0) as total')->value('total');
        } else {
            $totalSerrures = Salle::all()->reduce(function ($acc, $salle) {
                $arr = is_array($salle->dorma) ? $salle->dorma : [];
                return $acc + count($arr);
            }, 0);
        }

        $enseignantsCount = Enseignant::count();

        $today = now()->toDateString();
        $coursToday = Cours::whereDate('date', $today)->count();

        return Inertia::render('Home', [
            'dashboard' => [
                'enseignants' => $enseignantsCount,
                'salles' => $sallesCount,
                'serrures' => (int) $totalSerrures,
                'cours_today' => $coursToday,
                'last_sync_salles' => null,
                'last_sync_cours' => null,
            ],
        ]);
    }
}
