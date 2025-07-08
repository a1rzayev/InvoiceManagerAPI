<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Http\Controllers\Base\Controller;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(User::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => ['required', Rule::in(UserRole::values())],
            'is_active' => 'boolean'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) 
        {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        return response()->json($user);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) 
        {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => ['sometimes', Rule::in(UserRole::values())],
            'is_active' => 'sometimes|boolean'
        ]);

        if (isset($validated['password'])) 
        {
            $validated['password'] = Hash::make($validated['password']);
        }
        
        $user->update($validated);

        return response()->json($user);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) 
        {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function getAdmins(): JsonResponse
    {
        $admins = User::where('role', UserRole::ADMIN)->get(['id', 'name', 'email', 'phone', 'is_active']);
        return response()->json($admins);
    }

    public function getSellers(): JsonResponse
    {
        $sellers = User::where('role', UserRole::SELLER)->get(['id', 'name', 'email', 'phone', 'is_active']);
        return response()->json($sellers);
    }

    public function getClients(): JsonResponse
    {
        $clients = User::where('role', UserRole::CLIENT)->get(['id', 'name', 'email', 'phone', 'is_active']);
        return response()->json($clients);
    }
}
