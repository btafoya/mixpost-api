<?php

namespace Btafoya\MixpostApi\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Btafoya\MixpostApi\Http\Requests\CreateTokenRequest;

class TokenController extends ApiController
{
    /**
     * Create a new API token.
     *
     * @throws ValidationException
     */
    public function create(CreateTokenRequest $request): JsonResponse
    {
        $userModel = config('mixpost.user_model', \Inovector\Mixpost\Models\User::class);
        $user = $userModel::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Delete existing tokens if requested
        if ($request->boolean('revoke_existing', false)) {
            $user->tokens()->delete();
        }

        $abilities = $request->abilities ?? ['*'];
        $expiresAt = $this->getExpirationDate($request->expires_at);

        $token = $user->createToken(
            $request->token_name,
            $abilities,
            $expiresAt
        );

        return $this->created([
            'token' => $token->plainTextToken,
            'token_name' => $request->token_name,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
            'expires_at' => $expiresAt?->toIso8601String(),
        ], 'API token created successfully');
    }

    /**
     * List all tokens for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens->map(function ($token) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'expires_at' => $token->expires_at?->toIso8601String(),
                'created_at' => $token->created_at->toIso8601String(),
            ];
        });

        return $this->success($tokens);
    }

    /**
     * Delete a specific token.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $token = $request->user()->tokens()->where('id', $id)->first();

        if (! $token) {
            return $this->notFound('Token not found');
        }

        $token->delete();

        return $this->deleted('Token deleted successfully');
    }

    /**
     * Delete the current token (logout).
     */
    public function destroyCurrent(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->deleted('Current token revoked successfully');
    }

    /**
     * Get the expiration date for the token.
     */
    protected function getExpirationDate(?string $expiresAt): ?\DateTime
    {
        if (! $expiresAt) {
            $configExpiration = config('mixpost-api.token.expiration');
            if (! $configExpiration) {
                return null;
            }
            return now()->addMinutes($configExpiration);
        }

        return \DateTime::createFromFormat('Y-m-d H:i:s', $expiresAt) ?: null;
    }
}
