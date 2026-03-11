<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DemandeController;
use App\Http\Controllers\Admin\DemandeDecisionController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\PretController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\RegistrationController;

// ✅ Changed from 'auth' to 'auth:web' for session-based auth
Route::middleware(['auth:web', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Demandes
    Route::get('/demandes', [DemandeController::class, 'index'])->name('demandes.index');
    Route::get('/demandes/{id}', [DemandeController::class, 'show'])->name('demandes.show');

    // Employee registrations
    Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::post('/registrations/{id}/approve', [RegistrationController::class, 'approve'])->name('registrations.approve');
    Route::post('/registrations/{id}/reject', [RegistrationController::class, 'reject'])->name('registrations.reject');

    // Employee management
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::patch('/employees/{id}/direction', [EmployeeController::class, 'updateDirection'])->name('employees.direction.update');
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // Prets
    Route::get('/prets', [PretController::class, 'index'])->name('prets.index');
    Route::get('/prets/{id}', [PretController::class, 'show'])->name('prets.show');

    // Chat support
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{id}/messages', [ChatController::class, 'sendMessage'])->name('chat.messages.send');
    Route::delete('/chat/{id}', [ChatController::class, 'destroy'])->name('chat.destroy');

    Route::post('/demandes/{id}/approuver', [DemandeDecisionController::class, 'approuver'])
        ->name('demandes.approuver');

    Route::post('/demandes/{id}/rejeter', [DemandeDecisionController::class, 'rejeter'])
        ->name('demandes.rejeter');
});
