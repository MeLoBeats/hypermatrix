<?php

use App\Http\Controllers\CoursController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MatrixDebugController;
use App\Http\Controllers\SalleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/salles', [SalleController::class, 'index']);
Route::get('/cours', [CoursController::class, 'index']);
Route::get('/logs', [LogController::class, 'index']);
Route::get('/enseignants', [EnseignantController::class, 'index']);
Route::get('/test', [MatrixDebugController::class, 'sync']);
Route::get('/teste/{id}', [MatrixDebugController::class, 'createAccess']);
Route::get('/doors/{id}', [MatrixDebugController::class, 'doors']);
