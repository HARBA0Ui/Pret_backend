<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AideSocialeController;
use App\Http\Controllers\Api\DonsScolaireController;
use App\Http\Controllers\Api\SalaireCollectivesController;
use App\Http\Controllers\Api\RemboursementAnticipeController;
use App\Http\Controllers\Api\PretExceptionnelController;
use App\Http\Controllers\Api\PretHajjController;
use App\Http\Controllers\Api\PretController;
use App\Http\Controllers\Api\MensualiteController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\AdminChatController;

use App\Http\Controllers\My\DemandeLimitsController;

Route::get('/health', fn() => response()->json(['ok' => true]));

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('profile-picture', [AuthController::class, 'updateProfilePicture']);
    });
});

// routes/api.php ou web.php
Route::middleware(['auth:sanctum', 'employee.accepted'])->get(
    '/my/prets/{id}/recu',
    [\App\Http\Controllers\Api\PretPdfController::class, 'download']
);


Route::middleware(['auth:sanctum', 'employee.accepted'])->group(function () {

    // ✅ Limits endpoint
    Route::get('/my/demande-limits', DemandeLimitsController::class);

    /**
     * Employee-friendly endpoints (no client filtering)
     */
    Route::prefix('my')->group(function () {
        Route::get('aides-sociales', [AideSocialeController::class, 'myIndex']);
        Route::get('dons-scolaires', [DonsScolaireController::class, 'myIndex']);
        Route::get('salaires-collectives', [SalaireCollectivesController::class, 'myIndex']);
        Route::get('remboursements-anticipes', [RemboursementAnticipeController::class, 'myIndex']);
        Route::get('prets-exceptionnels', [PretExceptionnelController::class, 'myIndex']);
        Route::get('prets-hajj', [PretHajjController::class, 'myIndex']);

        Route::get('prets', [PretController::class, 'myIndex']);
        Route::get('mensualites', [MensualiteController::class, 'myIndex']);
        Route::get('mensualites/next-due', [MensualiteController::class, 'myNextDue']);
    });

    /**
     * Attachment routes
     */
    Route::prefix('attachments')->group(function () {
        Route::post('upload', [AttachmentController::class, 'upload']);
        Route::delete('{id}', [AttachmentController::class, 'destroy']);
        Route::get('{id}/download', [AttachmentController::class, 'download']);
    });

    /**
     * Chat (employee only)
     */
    Route::middleware('role:employee')->prefix('chat')->group(function () {
        Route::get('conversation', [ChatController::class, 'myConversation']);
        Route::get('messages', [ChatController::class, 'myMessages']);
        Route::post('messages', [ChatController::class, 'sendMessage']);
        Route::delete('conversation', [ChatController::class, 'deleteConversation']);
    });

    /**
     * Chat (admin only)
     */
    Route::middleware('role:admin')->prefix('admin/chat')->group(function () {
        Route::get('conversations', [AdminChatController::class, 'index']);
        Route::get('conversations/{id}', [AdminChatController::class, 'show']);
        Route::post('conversations/{id}/messages', [AdminChatController::class, 'sendMessage']);
        Route::delete('conversations/{id}', [AdminChatController::class, 'destroy']);
    });

    /**+
     * Existing API resources
     */
    Route::apiResource('aides-sociales', AideSocialeController::class);
    Route::apiResource('dons-scolaires', DonsScolaireController::class);
    Route::apiResource('salaires-collectives', SalaireCollectivesController::class);
    Route::apiResource('remboursements-anticipes', RemboursementAnticipeController::class);
    Route::apiResource('prets-exceptionnels', PretExceptionnelController::class);
    Route::apiResource('prets-hajj', PretHajjController::class);
    Route::apiResource('prets', PretController::class);
    Route::apiResource('mensualites', MensualiteController::class);
});
