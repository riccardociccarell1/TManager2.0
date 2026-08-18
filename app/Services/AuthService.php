<?php

namespace App\Services;
use app\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


/**
 * service that manages user authentication logic
 */

class AuthServices
{

/**
 * Register a new user 
 * 
 * the name and surname are coverted to uppercase
 * before saving the user into db
 *
 * @param array $data
 * @return User
 */
    public function registerUser(array $data): User
    {
        return User::create([
            'name' => strtoupper($data['name']),
            'email' => strtolower($data['email']),
            'password' => bcrypt($data['password']),
        ]);
    }

 /**
  * Login a user and create a authentication token
  *
  * @param array $credentials
  * @return User|null
  */   
    public function loginUser(array $data): array
    {
        //Search the user by email
        $user = User::where('email', strtolower($data['email']))->first();

        // Check if the user exists and the password is correct
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // Create a new token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the user and token
        return [
            'user' => $user,
            'token' => $token,
        ];
    }

}
