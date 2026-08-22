<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'AuthResponse',
    type: 'object',
    properties: [
        new OAT\Property(
            property: 'user',
            properties: [
                new OAT\Property(property: 'id', type: 'integer', example: 1),
                new OAT\Property(property: 'name', type: 'string', example: 'John Doe'),
                new OAT\Property(property: 'email', type: 'string', example: 'john@example.com'),
                new OAT\Property(property: 'is_admin', type: 'boolean', example: false),
            ],
            type: 'object'
        ),
        new OAT\Property(property: 'access_token', type: 'string', example: '1|abcdef...'),
        new OAT\Property(property: 'token_type', type: 'string', example: 'Bearer'),
    ]
)]
class AuthController extends Controller
{
    #[OAT\Post(
        path: '/api/register',
        summary: 'Register a new user',
        tags: ['Auth']
    )]
    #[OAT\RequestBody(
        required: true,
        content: new OAT\JsonContent(
            required: ['name', 'email', 'password'],
            properties: [
                new OAT\Property(property: 'name', type: 'string', example: 'John Doe'),
                new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                new OAT\Property(property: 'password', type: 'string', format: 'password', example: 'secret'),
            ]
        )
    )]
    #[OAT\Response(
        response: 200,
        description: 'User registered successfully',
        content: new OAT\JsonContent(ref: '#/components/schemas/AuthResponse')
    )]
    #[OAT\Response(response: 422, description: 'Validation Error')]
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return $this->respondWithToken($user);
    }

    #[OAT\Post(
        path: '/api/login',
        summary: 'Login user and return token',
        tags: ['Auth']
    )]
    #[OAT\RequestBody(
        required: true,
        content: new OAT\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                new OAT\Property(property: 'password', type: 'string', format: 'password', example: 'secret'),
            ]
        )
    )]
    #[OAT\Response(
        response: 200,
        description: 'User logged in successfully',
        content: new OAT\JsonContent(ref: '#/components/schemas/AuthResponse')
    )]
    #[OAT\Response(response: 422, description: 'Invalid credentials or Validation Error')]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->respondWithToken($user);
    }

    #[OAT\Get(
        path: '/api/user',
        summary: 'Get authenticated user',
        tags: ['Auth'],
        security: [['sanctum' => []]]
    )]
    #[OAT\Response(
        response: 200,
        description: 'User details',
        content: new OAT\JsonContent(
            properties: [
                new OAT\Property(property: 'id', type: 'integer'),
                new OAT\Property(property: 'name', type: 'string'),
                new OAT\Property(property: 'email', type: 'string'),
                new OAT\Property(property: 'is_admin', type: 'boolean'),
            ]
        )
    )]
    #[OAT\Response(response: 401, description: 'Unauthenticated')]
    public function user(Request $request)
    {
        return $request->user();
    }

    /**
     * Helper to format the authentication response.
     */
    private function respondWithToken(User $user)
    {
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new \App\Http\Resources\UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
