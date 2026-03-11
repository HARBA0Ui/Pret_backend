<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthService
{
    private const MATRICULE_MAX_ATTEMPTS = 500;

    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        try {
            $profilePicturePath = null;
            if (!empty($data['profilePicture']) && $data['profilePicture'] instanceof UploadedFile) {
                $profilePicturePath = $this->storeProfilePicture($data['profilePicture']);
            }

            $role = $data['role'] ?? 'employee';
            $matricule = $role === 'employee'
                ? $this->generateUniqueMatricule()
                : ($data['matricule'] ?? null);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $role,
                'isEmployeeAccepted' => $role === 'employee' ? false : true,
                'matricule' => $matricule,
                'salaire' => array_key_exists('salaire', $data) ? (float) $data['salaire'] : null,
                'date_joined' => $data['date_joined'] ?? null,
                'direction' => $data['direction'] ?? null,
                'profilePicturePath' => $profilePicturePath,
            ]);

            return $user;
        } catch (\Exception $e) {
            Log::error('User registration failed: ' . $e->getMessage());
            throw new \Exception('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login user and return token.
     */
    public function login(string $email, string $password): array
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user || !Hash::check($password, $user->password)) {
                throw new \Exception('Invalid credentials');
            }

            if (($user->role ?? '') === 'employee' && !$this->isEmployeeAccepted($user)) {
                throw new \Exception('Votre inscription est en attente de validation par un administrateur.');
            }

            $tokenResult = $user->createToken('auth_token');
            $fullToken = $tokenResult->plainTextToken;
            $tokenParts = explode('|', $fullToken, 2);
            $plainToken = isset($tokenParts[1]) ? $tokenParts[1] : $fullToken;

            return [
                'user' => $this->mapPublicUser($user),
                'token' => $plainToken,
            ];
        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Logout user (revoke current token).
     */
    public function logout(User $user): bool
    {
        try {
            $user->currentAccessToken()?->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            throw new \Exception('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get current authenticated user.
     */
    public function getCurrentUser(User $user): array
    {
        return $this->mapPublicUser($user);
    }

    /**
     * Public user payload.
     */
    public function mapPublicUser(User $user): array
    {
        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'isEmployeeAccepted' => $this->isEmployeeAccepted($user),
            'matricule' => $user->matricule ?? null,
            'salaire' => $user->salaire ?? null,
            'date_joined' => $user->date_joined ?? null,
            'direction' => $user->direction ?? null,
            'profilePicturePath' => $user->profilePicturePath ?? null,
            'profilePictureUrl' => $this->resolveProfilePictureUrl($user->profilePicturePath ?? null),
        ];
    }

    public function updateProfilePicture(User $user, UploadedFile $file): User
    {
        $newPath = $this->storeProfilePicture($file);
        $oldPath = $user->profilePicturePath;

        $user->update([
            'profilePicturePath' => $newPath,
        ]);

        if (!empty($oldPath) && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $user->fresh();
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Check if user is employee.
     */
    public function isEmployee(User $user): bool
    {
        return $user->role === 'employee';
    }

    private function storeProfilePicture(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = Str::uuid()->toString() . '.' . $extension;

        return $file->storeAs('profile-pictures', $filename, 'public');
    }

    private function resolveProfilePictureUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return url(Storage::url($path));
    }

    private function isEmployeeAccepted(User $user): bool
    {
        if (($user->role ?? '') !== 'employee') {
            return true;
        }

        $attributes = $user->getAttributes();
        if (!array_key_exists('isEmployeeAccepted', $attributes)) {
            // Backward compatibility: pre-existing employees are treated as accepted.
            return true;
        }

        return (bool) ($user->isEmployeeAccepted ?? false);
    }

    /**
     * Generate unique matricule in the format: AA000A (6 chars).
     */
    private function generateUniqueMatricule(): string
    {
        for ($i = 0; $i < self::MATRICULE_MAX_ATTEMPTS; $i++) {
            $candidate = $this->randomMatriculeCandidate();

            if (!User::where('matricule', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Impossible de generer un matricule unique. Reessayez.');
    }

    private function randomMatriculeCandidate(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $partA = $chars[random_int(0, 25)] . $chars[random_int(0, 25)];
        $partB = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
        $partC = $chars[random_int(0, 25)];

        return $partA . $partB . $partC;
    }
}
