<?php

namespace App\Http\Controllers;

use App\Services\MatrixService;
use App\Services\SyncMatrixPersonService;
use Carbon\Carbon;

class TestController extends Controller
{
    public function test(MatrixService $ms, SyncMatrixPersonService $sms)
    {
        $allDataToSync = $sms->sync();
        $results = [];
        
        foreach ($allDataToSync as $data) {
            $from = Carbon::parse($data->date)->subWeek();
            $until = Carbon::parse($data->date);

            $newAccess = $ms->createAccessPermissionByMatricule(
                $data->id, 
                $data->salle->dorma, 
                $from, 
                $until
            );
            
            $results[] = [
                'access' => $newAccess,
                'enseignants' => $data->enseignants->pluck('matricule')
            ];
        }
        
        return response()->json($results);
    }

    public function testById(string $id, MatrixService $ms)
    {
        return $ms->createAccessPermissionByMatricule(
            $id, 
            "7181", 
            Carbon::now(), 
            Carbon::now()->addMonth()
        );
    }
}
