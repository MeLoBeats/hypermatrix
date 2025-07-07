<?php

use App\Jobs\SyncSalleCoursesJob;
use App\Models\Cours;
use App\Models\Enseignant;
use App\Models\Salle;
use App\Services\HyperplanningRestService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('crée les cours et associe les enseignants', function () {
    Log::spy(); // Évite les logs pendant le test

    // 🧪 1. Salle factice
    $salle = Salle::factory()->create(['hp_id' => 123, 'dorma' => "[4848,4786]", "libelle" => "test"]);

    // 🧪 2. Dates
    $debut = Carbon::now();
    $fin = $debut->copy()->addMonth();
    $jourCours = $debut->copy()->addDays(1);

    // 🧪 3. Mock du service Hyperplanning
    $hpMock = Mockery::mock(HyperplanningRestService::class);
    $this->app->instance(HyperplanningRestService::class, $hpMock);

    $hpMock->shouldReceive('getCoursesByRoomBetweenDates')
        ->once()
        ->with(123, $debut->format('Y-m-d'), $fin->format('Y-m-d'))
        ->andReturn([111]);

    $hpMock->shouldReceive('getCoursesData')
        ->once()
        ->with([111])
        ->andReturn([
            [[
                'cle' => 111,
                'jour_heure_debut' => $jourCours->toDateTimeString(),
                'enseignants' => ['hp-ens-1']
            ]]
        ]);

    $hpMock->shouldReceive('getEnseignantsData')
        ->once()
        ->with(['hp-ens-1'])
        ->andReturn([
            [
                'cle' => 'hp-ens-1',
                'code' => 'ENS001',
                'nom' => 'Durand',
                'prenom' => 'Jean',
            ]
        ]);

    // 🎯 4. Lancer le job
    (new SyncSalleCoursesJob($salle->id))->handle($hpMock);

    // ✅ 5. Vérifications
    $cours = Cours::first();
    expect($cours)->not->toBeNull()
        ->and($cours->hp_id)->toBe('111')
        ->and($cours->salle_id)->toBe($salle->id);

    $enseignant = Enseignant::where('matricule', 'ENS001')->first();
    expect($enseignant)->not->toBeNull()
        ->and($cours->enseignants->pluck('id'))->toContain($enseignant->id);
});
