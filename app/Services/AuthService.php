<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Service that manages user authentication logic.
 */
class AuthService
{
    /**
     * Register a new user.
     *
     * The name and surname are converted to uppercase
     * before saving the user into the database.
     *
     * @param array $data
     * @return User
     */
    public function registerUser(array $data): User
    {
        return User::create([
            'name' => strtoupper($data['name']),
            'surname' => strtoupper($data['surname']),
            'email' => strtolower($data['email']),
            'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * Login a user and create an authentication token.
     *
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function loginUser(array $data): array
    {
        // Search the user by email.
        $user = User::where(
            'email',
            strtolower($data['email'])
        )->first();

        // Check if the user exists and the password is correct.
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // Create a new authentication token for the user.
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the authenticated user and token.
        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    
    /**
     * Logout a user by revoking their authentication tokens.
     *
     * @param User $user
     * @return void
     */
    public function logoutUser(User $user): void
    {
        // Revoke all tokens for the user.
        $user->tokens()->delete();
    }
}