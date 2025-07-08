<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Enums\UserRole;

class CheckAnyRole
{
    public function handle(Request $request, Closure $next, string $roles): JsonResponse
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
        $requiredRoles = explode('|', $roles);
        $validRoles = [];
        foreach ($requiredRoles as $role) 
        {
            $enumRole = UserRole::tryFrom($role);
            if ($enumRole) 
            {
                $validRoles[] = $enumRole;
            }
        }
        if (empty($validRoles)) 
        {
            return response()->json([
                'success' => false,
                'message' => 'Invalid roles specified',
                'code' => 'INVALID_ROLES'
            ], 400);
        }
        $hasAnyRole = false;
        foreach ($validRoles as $role) 
        {
            if ($user->hasRole($role)) 
            {
                $hasAnyRole = true;
                break;
            }
        }
        if (!$hasAnyRole) 
        {
            $roleLabels = array_map(fn($role) => $role->label(), $validRoles);
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions. Required one of: ' . implode(', ', $roleLabels),
                'code' => 'INSUFFICIENT_PERMISSIONS'
            ], 403);
        }

        return $next($request);
    }
} 