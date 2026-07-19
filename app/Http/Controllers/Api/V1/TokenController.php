<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Prompt 18: Sanctum token management.
 *
 * POST /api/v1/tokens  — issue a new Sanctum token (login).
 * DELETE /api/v1/tokens — revoke the current token (logout).
 * GET /api/v1/user     — return the authenticated user.
 */
class TokenController extends Controller
{
    /**
     * Issue a Sanctum API token for valid credentials.
     *
     * Request body: { "email": "...", "password": "...", "device_name": "..." }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke existing tokens with the same device name to prevent accumulation
        $user->tokens()->where('name', $request->device_name)->delete();

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user->load('donorProfile')),
        ], 201);
    }

    /**
     * Revoke the current Sanctum token (logout).
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revoked successfully.']);
    }

    /**
     * Return the currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(
            new UserResource($request->user()->load('donorProfile'))
        );
    }
}
