<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserCollection;

class UserController extends ApiController
{
    /**
     * Display a listing of users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate($request->per_page ?? 15);

        return $this->sendResponse(
            new UserCollection($users),
            'Users retrieved successfully'
        );
    }

    /**
     * Display the specified user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user)
    {
        return $this->sendResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Update the specified user.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return $this->sendResponse(
            new UserResource($user),
            'User updated successfully'
        );
    }

    /**
     * Remove the specified user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->sendResponse(
            null,
            'User deleted successfully'
        );
    }
}