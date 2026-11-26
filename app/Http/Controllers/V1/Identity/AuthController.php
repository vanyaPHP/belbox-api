<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Identity;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::query()->where('email', $request->input('email'))->first();
        if (! $user) {
            return response()->json([
                'error' => 'User not found.',
            ], 404);
        }

        if (! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'error' => 'Wrong password.',
            ], 422);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'token' => $token,
        ]);
    }

    public function me(): JsonResponse
    {
        $user = auth()->user()->load(['roles', 'employee']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'employee_id' => $user->employee->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'roles' => $user->rolesList(),
                'permissions' => $user->permissions(),
            ],
        ]);
    }

    public function logout(): JsonResponse
    {
        $user = auth()->user();
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
