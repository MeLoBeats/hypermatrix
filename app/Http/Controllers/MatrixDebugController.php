<?php

namespace App\Http\Controllers;

use App\Services\MatrixService;
use App\Services\SyncMatrixPersonService;
use Carbon\Carbon;

class MatrixDebugController extends Controller
{
    public function sync(MatrixService $ms, SyncMatrixPersonService $sms)
    {
        $allDataToSync = $sms->sync();
        foreach ($allDataToSync as $data) {
            $from = Carbon::parse($data->date)->subWeek();
            $until = Carbon::parse($data->date);

            $newAccess = $ms->createAccessPermissionByMatricule(
                $data->id,
                $data->salle->dorma,
                $from,
                $until
            );
            dump($newAccess, $data->enseignants->pluck('matricule'));
        }
    }

    public function createAccess(string $id, MatrixService $ms)
    {
        return $ms->createAccessPermissionByMatricule($id, '7181', Carbon::now(), Carbon::now()->addMonth());
    }

    public function doors(int $id, MatrixService $ms)
    {
        return $ms->xmlToArrayFromString($ms->getDoors($id))['Name'];
    }
}
