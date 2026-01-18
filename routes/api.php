<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AideSocialeController;
use App\Http\Controllers\Api\DonsScolaireController;
use App\Http\Controllers\Api\SalaireCollectivesController;
use App\Http\Controllers\Api\RemboursementAnticipeController;
use App\Http\Controllers\Api\PretController;
use App\Http\Controllers\Api\MensualiteController;

Route::get('/health', fn () => response()->json(['ok' => true]));

Route::apiResource('aides-sociales', AideSocialeController::class);
Route::apiResource('dons-scolaires', DonsScolaireController::class);
Route::apiResource('salaires-collectives', SalaireCollectivesController::class);
Route::apiResource('remboursements-anticipes', RemboursementAnticipeController::class);

Route::apiResource('prets', PretController::class);
Route::apiResource('mensualites', MensualiteController::class);
