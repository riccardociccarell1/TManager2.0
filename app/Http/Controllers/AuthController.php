<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;


/**
 * Controller that manages user authentication.
 */
class AuthController extends Controller
{

    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request) : JsonResponse
    {
        $user = $this->authService->registerUser($request->validated());

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
        ], 201);
    }


    /**
     * Login a user and create an authentication token.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request) : JsonResponse
    {
        $result = $this->authService->loginUser($request->validated());

        return response()->json([
            'message' => 'User logged in successfully.',
            'user' => $result['user'],
            'token' => $result['token'],
        ], 200);

    }

}
