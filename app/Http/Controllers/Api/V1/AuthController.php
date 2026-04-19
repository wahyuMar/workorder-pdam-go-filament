<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        if (Auth::check()) {
            return response()->json(['message' => 'Already authenticated.'], 403);
        }

        $user = DB::transaction(function () use ($request) {
            try {
                $user = User::create($request->validated());
            } catch (UniqueConstraintViolationException) {
                throw ValidationException::withMessages([
                    'email' => [__('validation.unique', ['attribute' => 'email'])],
                ]);
            }

            Role::findOrCreate('customer', 'web');
            $user->assignRole('customer');

            return $user;
        });

        Auth::login($user);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (Auth::check()) {
            return response()->json(['message' => 'Already authenticated.'], 403);
        }

        if (! Auth::attempt($request->validated())) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        }

        $user = Auth::user();

        if (! $user->hasRole('customer')) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        }

        $request->session()->regenerate();

        return (new UserResource($user))
            ->response()
            ->setStatusCode(200);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
