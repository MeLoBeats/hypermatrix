<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\CoursController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Page d'accueil
Route::get('/', [HomeController::class, 'index']);

// Gestion des salles
Route::get('/salles', [SalleController::class, 'index']);

// Gestion des cours
Route::get('/cours', [CoursController::class, 'index']);

// Gestion des enseignants
Route::get('/enseignants', [EnseignantController::class, 'index']);

// Logs
Route::get('/logs', function () {
    return Inertia\Inertia::render('Logs');
});

// Routes de test
Route::prefix('test')->group(function () {
    Route::get('/', [TestController::class, 'test']);
    Route::get('/{id}', [TestController::class, 'testById']);
});
