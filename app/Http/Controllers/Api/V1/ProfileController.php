<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Profile\UpdateRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return (new UserResource($request->user()))
            ->response()
            ->setStatusCode(200);
    }

    public function update(UpdateRequest $request): JsonResponse
    {
        try {
            $request->user()->update($request->validated());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'email' => [__('validation.unique', ['attribute' => 'email'])],
            ]);
        }

        return (new UserResource($request->user()->fresh()))
            ->response()
            ->setStatusCode(200);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $password = $request->validated()['password'];

        $request->user()->update([
            'password' => $password,
        ]);

        // Regenerate session to reflect password change
        $request->session()->regenerate();

        return response()->json(['message' => 'Password changed successfully.']);
    }
}
