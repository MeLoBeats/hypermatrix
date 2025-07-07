<?php

use App\Models\Salle;
use App\Services\RoomSyncService;
use App\Services\HyperplanningRestService;
use Illuminate\Database\Schema\Blueprint;
use function Pest\Laravel\mock;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('salles', function (Blueprint $table) {
        $table->id();
        $table->string('hp_id')->unique();
        $table->string('libelle');
        $table->json('dorma')->nullable();
        $table->timestamps();
    });
});


it('synchronizes rooms with Dorma locks correctly', function () {
    // Mock des réponses API
    $mockedSalles = [
        ['cle' => 'ROOM001', 'nom' => 'Salle A'],
        ['cle' => 'ROOM002', 'nom' => 'Salle B'],
    ];

    $mockedDormaIds = [
        'ROOM001' => [123, 456],
        'ROOM002' => [],
    ];

    /** @var \Mockery\MockInterface&\App\Services\HyperplanningRestService $hpMock */
    $hpMock = mock(HyperplanningRestService::class);
    $hpMock->shouldReceive('getSalles')->once()->andReturn($mockedSalles);
    $hpMock->shouldReceive('getDormaIdsForOneRoom')
        ->with('ROOM001')->andReturn($mockedDormaIds['ROOM001']);
    $hpMock->shouldReceive('getDormaIdsForOneRoom')
        ->with('ROOM002')->andReturn($mockedDormaIds['ROOM002']);

    // Exécution de la logique
    $service = new RoomSyncService($hpMock);
    $result = $service->sync();

    // Vérification
    expect(Salle::where('hp_id', 'ROOM001')->exists())->toBeTrue();
    expect(Salle::where('hp_id', 'ROOM002')->exists())->toBeFalse();
    expect($result)->toHaveKey('ROOM001');
    expect($result['ROOM001'])->toBe([123, 456]);
})->group('sync');
