<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Auth\WebAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check() && (Auth::user()->role ?? '') === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
});

// Web Login Routes
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// attachment download route (protected by auth)
Route::middleware('auth:web')->group(function () {
    Route::get('/attachments/{id}/view', '\App\Http\Controllers\AttachmentController@view')
        ->name('attachments.view');
    Route::get('/attachments/{id}/download', '\App\Http\Controllers\AttachmentController@download')
        ->name('attachments.download');
});
