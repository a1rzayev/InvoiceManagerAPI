<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Http\Controllers\Base\Controller;

/**
 * @OA\Schema(
 *     schema="User",
 *     required={"name", "email", "password", "role"},
 *     @OA\Property(property="id", type="string", format="uuid", description="User unique identifier"),
 *     @OA\Property(property="name", type="string", maxLength=255, description="User's full name"),
 *     @OA\Property(property="email", type="string", format="email", description="User's email address"),
 *     @OA\Property(property="phone", type="string", maxLength=20, nullable=true, description="User's phone number"),
 *     @OA\Property(property="address", type="string", maxLength=500, nullable=true, description="User's address"),
 *     @OA\Property(property="role", type="string", enum={"admin", "seller", "client"}, description="User's role"),
 *     @OA\Property(property="is_active", type="boolean", description="Whether the user is active"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="User creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="User last update timestamp")
 * )
 * 
 * @OA\Schema(
 *     schema="UserCreateRequest",
 *     required={"name", "email", "password", "role"},
 *     @OA\Property(property="name", type="string", maxLength=255, description="User's full name"),
 *     @OA\Property(property="email", type="string", format="email", description="User's email address"),
 *     @OA\Property(property="password", type="string", minLength=8, description="User's password"),
 *     @OA\Property(property="phone", type="string", maxLength=20, nullable=true, description="User's phone number"),
 *     @OA\Property(property="address", type="string", maxLength=500, nullable=true, description="User's address"),
 *     @OA\Property(property="role", type="string", enum={"admin", "seller", "client"}, description="User's role"),
 *     @OA\Property(property="is_active", type="boolean", description="Whether the user is active")
 * )
 * 
 * @OA\Schema(
 *     schema="UserUpdateRequest",
 *     @OA\Property(property="name", type="string", maxLength=255, description="User's full name"),
 *     @OA\Property(property="email", type="string", format="email", description="User's email address"),
 *     @OA\Property(property="password", type="string", minLength=8, description="User's password"),
 *     @OA\Property(property="phone", type="string", maxLength=20, nullable=true, description="User's phone number"),
 *     @OA\Property(property="address", type="string", maxLength=500, nullable=true, description="User's address"),
 *     @OA\Property(property="role", type="string", enum={"admin", "seller", "client"}, description="User's role"),
 *     @OA\Property(property="is_active", type="boolean", description="Whether the user is active")
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     description="Retrieve a list of all users in the system",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="List of users retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(User::all());
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     description="Create a new user with the provided information",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get user by ID",
     *     description="Retrieve a specific user by their ID",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) 
        {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        return response()->json($user);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update user",
     *     description="Update an existing user's information",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete user",
     *     description="Delete a user from the system",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/users/admins/list",
     *     summary="Get all admins",
     *     description="Retrieve a list of all admin users",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="List of admin users retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="phone", type="string", nullable=true),
     *                 @OA\Property(property="is_active", type="boolean")
     *             )
     *         )
     *     )
     * )
     */
    public function getAdmins(): JsonResponse
    {
        $admins = User::where('role', UserRole::ADMIN)->get(['id', 'name', 'email', 'phone', 'is_active']);
        return response()->json($admins);
    }

    /**
     * @OA\Get(
     *     path="/api/users/sellers/list",
     *     summary="Get all sellers",
     *     description="Retrieve a list of all seller users",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="List of seller users retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="phone", type="string", nullable=true),
     *                 @OA\Property(property="is_active", type="boolean")
     *             )
     *         )
     *     )
     * )
     */
    public function getSellers(): JsonResponse
    {
        $sellers = User::where('role', UserRole::SELLER)->get(['id', 'name', 'email', 'phone', 'is_active']);
        return response()->json($sellers);
    }

    /**
     * @OA\Get(
     *     path="/api/users/clients/list",
     *     summary="Get all clients",
     *     description="Retrieve a list of all client users",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="List of client users retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="phone", type="string", nullable=true),
     *                 @OA\Property(property="is_active", type="boolean")
     *             )
     *         )
     *     )
     * )
     */
    public function getClients(): JsonResponse
    {
        $clients = User::where('role', UserRole::CLIENT)->get(['id', 'name', 'email', 'phone', 'is_active']);
        return response()->json($clients);
    }
}
