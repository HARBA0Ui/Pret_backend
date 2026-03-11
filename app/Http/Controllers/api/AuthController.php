<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'profilePicture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'salaire' => 'nullable|numeric|min:0',
            'date_joined' => 'nullable|date',
            'direction' => 'required|string|in:commercial,technique,financiere,rh,operations',
        ]);

        // Public registration is employee-only and requires admin approval.
        $data['role'] = 'employee';

        $user = $this->authService->register($data);

        return response()->json([
            'message' => 'Inscription enregistree. En attente de validation admin.',
            'user' => $this->authService->mapPublicUser($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $result = $this->authService->login($data['email'], $data['password']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'user' => $this->authService->mapPublicUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $this->authService->logout($user);

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function updateProfilePicture(Request $request)
    {
        $data = $request->validate([
            'profilePicture' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $updated = $this->authService->updateProfilePicture($user, $data['profilePicture']);

        return response()->json([
            'message' => 'Photo de profil mise a jour.',
            'user' => $this->authService->mapPublicUser($updated),
        ]);
    }
}
