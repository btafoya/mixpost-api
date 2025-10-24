<?php

namespace Btafoya\MixpostApi\Http\Controllers\Api;

use Btafoya\MixpostApi\Http\Resources\AccountResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inovector\Mixpost\Models\Account;

class AccountsController extends ApiController
{
    /**
     * List all social media accounts
     */
    public function index(): JsonResponse
    {
        $accounts = Account::latest()->get();

        return AccountResource::collection($accounts)->response();
    }

    /**
     * Get a single account by ID
     */
    public function show(int $id): JsonResponse
    {
        $account = Account::findOrFail($id);

        return (new AccountResource($account))->response();
    }

    /**
     * Update an account
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        $account = Account::findOrFail($id);

        if ($request->has('name')) {
            $account->update([
                'name' => $request->name,
            ]);
        }

        return (new AccountResource($account))
            ->additional(['message' => 'Account updated successfully'])
            ->response();
    }

    /**
     * Delete an account
     */
    public function destroy(int $id): JsonResponse
    {
        $account = Account::findOrFail($id);

        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ]);
    }
}
