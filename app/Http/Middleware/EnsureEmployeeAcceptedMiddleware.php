<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmployeeAcceptedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && ($user->role ?? '') === 'employee') {
            $attributes = $user->getAttributes();
            $isAccepted = array_key_exists('isEmployeeAccepted', $attributes)
                ? (bool) ($user->isEmployeeAccepted ?? false)
                : true;

            if (!$isAccepted) {
                return response()->json([
                    'message' => 'Votre compte employe est en attente de validation.',
                ], 403);
            }
        }

        return $next($request);
    }
}
