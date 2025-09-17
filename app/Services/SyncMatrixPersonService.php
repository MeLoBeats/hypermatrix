<?php

namespace App\Services;

use App\Models\Cours;
use Carbon\Carbon;

class SyncMatrixPersonService
{
    public function sync()
    {
        $start = Carbon::now()->subMonth(5);
        $end = $start->copy()->addMonth();
        $coursToSync = Cours::whereBetween("date", [$start, $end])->whereHas('enseignants', function ($query) {
            $query->where('cours_enseignants.active', '!=', true);
        })->get();
        return $coursToSync;
    }
}
