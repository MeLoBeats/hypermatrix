<?php

use App\Models\Enseignant;
use App\Models\Salle;
use App\Services\HyperplanningRestService;
use App\Services\MatrixService;
use App\Services\SyncMatrixPersonService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
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

Route::get('/test/{id}', function (string $id, MatrixService $ms) {
    return $ms->createAccessPermissionByMatricule($id, "7181", Carbon::now(), Carbon::now()->addMonth());
});
