<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CompteController;

Route::post('/compte', [CompteController::class, 'crearCompte']);
Route::post('/compte/{id}/ingres', [CompteController::class, 'ingres']);
Route::post('/compte/{id}/retirada', [CompteController::class, 'retirada']);
Route::post('/transferencia', [CompteController::class, 'transferencia']);
