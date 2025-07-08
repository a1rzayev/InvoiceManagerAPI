<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Enums\UserRole;

class CheckRole
{

    public function handle(Request $request, Closure $next, string $role): JsonResponse
    {
        if (!auth()->check()) 
        {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'code' => 'UNAUTHENTICATED'
            ], 401);
        }
        $user = auth()->user();
        $requiredRole = UserRole::tryFrom($role);
        if (!$requiredRole) 
        {
            return response()->json([
                'success' => false,
                'message' => 'Invalid role specified',
                'code' => 'INVALID_ROLE'
            ], 400);
        }
        if (!$user->hasRole($requiredRole)) 
        {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions. Required role: ' . $requiredRole->label(),
                'code' => 'INSUFFICIENT_PERMISSIONS'
            ], 403);
        }

        return $next($request);
    }
} 