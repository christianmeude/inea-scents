<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OAT;

class AuthController extends Controller
{
    #[OAT\Post(
        path: "/register",
        summary: "Register a new user",
        tags: ["Auth"]
    )]
    #[OAT\RequestBody(
        required: true,
        content: new OAT\JsonContent(
            required: ["name", "email", "password"],
            properties: [
                new OAT\Property(property: "name", type: "string", example: "John Doe"),
                new OAT\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                new OAT\Property(property: "password", type: "string", format: "password", example: "secret")
            ]
        )
    )]
    #[OAT\Response(
        response: 200,
        description: "User registered successfully",
        content: new OAT\JsonContent(
            properties: [
                new OAT\Property(
                    property: "user",
                    properties: [
                        new OAT\Property(property: "id", type: "integer", example: 1),
                        new OAT\Property(property: "name", type: "string", example: "John Doe"),
                        new OAT\Property(property: "email", type: "string", example: "john@example.com")
                    ],
                    type: "object"
                ),
                new OAT\Property(property: "token", type: "string", example: "1|abcdef...")
            ]
        )
    )]
    #[OAT\Response(response: 422, description: "Validation Error")]
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

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    #[OAT\Post(
        path: "/login",
        summary: "Login user and return token",
        tags: ["Auth"]
    )]
    #[OAT\RequestBody(
        required: true,
        content: new OAT\JsonContent(
            required: ["email", "password"],
            properties: [
                new OAT\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                new OAT\Property(property: "password", type: "string", format: "password", example: "secret")
            ]
        )
    )]
    #[OAT\Response(
        response: 200,
        description: "User logged in successfully",
        content: new OAT\JsonContent(
            properties: [
                new OAT\Property(
                    property: "user",
                    properties: [
                        new OAT\Property(property: "id", type: "integer", example: 1),
                        new OAT\Property(property: "name", type: "string", example: "John Doe"),
                        new OAT\Property(property: "email", type: "string", example: "john@example.com")
                    ],
                    type: "object"
                ),
                new OAT\Property(property: "token", type: "string", example: "1|abcdef...")
            ]
        )
    )]
    #[OAT\Response(response: 401, description: "Invalid credentials")]
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

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
}
