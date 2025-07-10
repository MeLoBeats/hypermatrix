<?php

use App\Models\Enseignant;
use App\Models\Salle;
use App\Services\HyperplanningRestService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
