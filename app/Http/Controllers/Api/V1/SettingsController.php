<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settings\UpdateProfileRequest;
use App\Http\Requests\Api\V1\Settings\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Settings\UpdateNotificationsRequest;
use App\Http\Requests\Api\V1\Settings\StoreUserRequest;
use App\Http\Requests\Api\V1\Settings\UpdateUserRequest;
use App\Models\User;
use App\Models\Role;
use App\Models\Team;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DataExport;
use App\Imports\DataImport;

class SettingsController extends Controller
{
    /**
     * Get user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load(['role', 'team', 'currentTeam']);
        
        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $updateData = $request->validated();
        
        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $updateData['profile_photo_path'] = $path;
            
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
        }
        
        $user->update($updateData);
        $user->load(['role', 'team', 'currentTeam']);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(UpdateNotificationsRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $settings = $user->settings ?? [];
        $settings['notifications'] = $request->validated();
        
        $user->update(['settings' => $settings]);

        return response()->json([
            'message' => 'Notification preferences updated successfully',
            'settings' => $settings,
        ]);
    }

    /**
     * Get all users (Admin/Manager only)
     */
    public function users(Request $request): JsonResponse
    {
        $this->authorize('manage-users');
        
        $users = User::with(['role', 'team', 'currentTeam'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($users);
    }

    /**
     * Store new user (Admin/Manager only)
     */
    public function storeUser(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('manage-users');
        
        $user = User::create([
            ...$request->validated(),
            'password' => Hash::make($request->password),
        ]);

        $user->load(['role', 'team', 'currentTeam']);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Get specific user (Admin/Manager only)
     */
    public function showUser(User $user): JsonResponse
    {
        $this->authorize('manage-users');
        
        $user->load(['role', 'team', 'currentTeam', 'assignedLeads', 'createdLeads']);

        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Update user (Admin/Manager only)
     */
    public function updateUser(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('manage-users');
        
        $updateData = $request->validated();
        
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }
        
        $user->update($updateData);
        $user->load(['role', 'team', 'currentTeam']);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Delete user (Admin/Manager only)
     */
    public function destroyUser(User $user): JsonResponse
    {
        $this->authorize('manage-users');
        
        // Prevent deletion of self
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Cannot delete your own account',
            ], 422);
        }
        
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Get available roles
     */
    public function roles(): JsonResponse
    {
        $roles = Role::orderBy('name')->get();

        return response()->json([
            'roles' => $roles
        ]);
    }

    /**
     * Get available teams
     */
    public function teams(): JsonResponse
    {
        $teams = Team::orderBy('name')->get();

        return response()->json([
            'teams' => $teams
        ]);
    }

    /**
     * Get integrations
     */
    public function integrations(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $integrations = Integration::where('user_id', $user->id)
            ->orWhereNull('user_id') // Global integrations
            ->orderBy('service_name')
            ->get();

        return response()->json([
            'integrations' => $integrations
        ]);
    }

    /**
     * Connect to external service
     */
    public function connectIntegration(Request $request, string $service): JsonResponse
    {
        $request->validate([
            'access_token' => 'required|string',
            'refresh_token' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'settings' => 'nullable|array',
        ]);

        $user = $request->user();
        
        // Check if integration already exists
        $integration = Integration::where('user_id', $user->id)
            ->where('service_name', $service)
            ->first();

        if ($integration) {
            $integration->update([
                'access_token' => encrypt($request->access_token),
                'refresh_token' => $request->refresh_token ? encrypt($request->refresh_token) : null,
                'expires_at' => $request->expires_at,
                'status' => 'connected',
                'settings' => $request->settings ?? [],
            ]);
        } else {
            $integration = Integration::create([
                'user_id' => $user->id,
                'service_name' => $service,
                'access_token' => encrypt($request->access_token),
                'refresh_token' => $request->refresh_token ? encrypt($request->refresh_token) : null,
                'expires_at' => $request->expires_at,
                'status' => 'connected',
                'settings' => $request->settings ?? [],
            ]);
        }

        return response()->json([
            'message' => 'Integration connected successfully',
            'integration' => $integration,
        ]);
    }

    /**
     * Disconnect from external service
     */
    public function disconnectIntegration(string $service): JsonResponse
    {
        $user = auth()->user();
        
        $integration = Integration::where('user_id', $user->id)
            ->where('service_name', $service)
            ->first();

        if ($integration) {
            $integration->update(['status' => 'disconnected']);
        }

        return response()->json([
            'message' => 'Integration disconnected successfully',
        ]);
    }

    /**
     * Check integration status
     */
    public function integrationStatus(string $service): JsonResponse
    {
        $user = auth()->user();
        
        $integration = Integration::where('user_id', $user->id)
            ->where('service_name', $service)
            ->first();

        if (!$integration) {
            return response()->json([
                'status' => 'not_connected',
                'message' => 'Integration not found',
            ]);
        }

        return response()->json([
            'status' => $integration->status,
            'is_active' => $integration->isActive(),
            'is_expired' => $integration->isExpired(),
            'expires_at' => $integration->expires_at,
        ]);
    }

    /**
     * Import data
     */
    public function importData(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            'type' => 'required|in:leads,users,tasks',
        ]);

        try {
            Excel::import(new DataImport($request->type), $request->file('file'));
            
            return response()->json([
                'message' => 'Data imported successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Export data
     */
    public function exportData(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:leads,users,tasks,all',
            'format' => 'required|in:csv,xlsx,pdf',
        ]);

        $fileName = $request->type . '_export_' . now()->format('Y_m_d_H_i_s') . '.' . $request->format;

        try {
            Excel::store(new DataExport($request->type), $fileName, 'public');
            
            return response()->json([
                'message' => 'Export completed successfully',
                'download_url' => asset('storage/' . $fileName),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Export failed: ' . $e->getMessage(),
            ], 422);
        }
    }
}
