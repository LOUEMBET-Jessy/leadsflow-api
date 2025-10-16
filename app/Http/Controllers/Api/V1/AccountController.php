<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Get account information
     */
    public function show(Request $request): JsonResponse
    {
        $account = $request->user()->account;
        
        return response()->json([
            'account' => $account,
            'stats' => [
                'users_count' => $account->users()->count(),
                'leads_count' => $account->leads()->count(),
                'pipelines_count' => $account->pipelines()->count(),
                'integrations_count' => $account->integrations()->count(),
            ]
        ]);
    }

    /**
     * Update account settings
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain' => 'sometimes|nullable|string|max:255',
            'settings' => 'sometimes|array',
        ]);

        $account = $request->user()->account;
        $account->update($request->only(['name', 'domain', 'settings']));

        return response()->json([
            'message' => 'Account updated successfully',
            'account' => $account,
        ]);
    }

    /**
     * Get account statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $account = $request->user()->account;
        
        $stats = [
            'users' => [
                'total' => $account->users()->count(),
                'active' => $account->users()->where('is_active', true)->count(),
                'by_role' => $account->users()
                    ->selectRaw('role, count(*) as count')
                    ->groupBy('role')
                    ->get()
                    ->pluck('count', 'role'),
            ],
            'leads' => [
                'total' => $account->leads()->count(),
                'by_status' => $account->leads()
                    ->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->get()
                    ->pluck('count', 'status'),
                'by_source' => $account->leads()
                    ->selectRaw('source, count(*) as count')
                    ->whereNotNull('source')
                    ->groupBy('source')
                    ->get()
                    ->pluck('count', 'source'),
                'recent' => $account->leads()
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count(),
            ],
            'pipelines' => [
                'total' => $account->pipelines()->count(),
                'active' => $account->pipelines()->where('is_active', true)->count(),
            ],
            'integrations' => [
                'total' => $account->integrations()->count(),
                'active' => $account->integrations()->where('is_active', true)->count(),
                'connected' => $account->integrations()
                    ->where('is_active', true)
                    ->whereNotNull('config->access_token')
                    ->count(),
            ],
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Get account users
     */
    public function users(Request $request): JsonResponse
    {
        $account = $request->user()->account;
        $users = $account->users()
            ->with(['assignedLeads'])
            ->paginate(20);

        return response()->json($users);
    }

    /**
     * Create a new user in the account
     */
    public function createUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:Admin,Manager,Commercial,Marketing,GestLead',
            'phone' => 'nullable|string|max:20',
        ]);

        $account = $request->user()->account;
        
        // Vérifier les permissions (seuls les admins peuvent créer des utilisateurs)
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = $account->users()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Update user in the account
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:Admin,Manager,Commercial,Marketing,GestLead',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        // Vérifier que l'utilisateur appartient au même compte
        if ($user->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Vérifier les permissions
        if (!$request->user()->isAdmin() && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->update($request->only(['name', 'email', 'role', 'phone', 'is_active']));

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Delete user from the account
     */
    public function deleteUser(Request $request, User $user): JsonResponse
    {
        // Vérifier que l'utilisateur appartient au même compte
        if ($user->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Vérifier les permissions
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Empêcher la suppression de soi-même
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot delete your own account'], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
