<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\PipelineController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\AiInsightController;

/*
|--------------------------------------------------------------------------
| API Routes V1
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Public lead capture routes (with API key validation)
Route::prefix('leads/capture')->group(function () {
    Route::post('web-form', [LeadController::class, 'captureWebForm']);
    Route::post('email', [LeadController::class, 'captureEmail']);
});

// Webhook routes (public, with signature validation)
Route::prefix('webhooks')->group(function () {
    Route::post('{endpoint}', [\App\Http\Controllers\Api\V1\WebhookController::class, 'handle']);
});

// Protected routes (authentication required)
Route::middleware('auth.simple')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('user', [AuthController::class, 'user']);
        Route::post('2fa/enable', [AuthController::class, 'enable2FA']);
        Route::post('2fa/disable', [AuthController::class, 'disable2FA']);
        Route::post('2fa/verify', [AuthController::class, 'verify2FA']);
    });

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('summary', [DashboardController::class, 'summary']);
        Route::get('stats', [DashboardController::class, 'stats']);
        Route::get('activity', [DashboardController::class, 'activity']);
        Route::get('funnel', [DashboardController::class, 'funnel']);
        Route::get('charts', [DashboardController::class, 'charts']);
        Route::get('recent-leads', [DashboardController::class, 'recentLeads']);
        Route::get('daily-tasks', [DashboardController::class, 'dailyTasks']);
        Route::get('team-performance', [DashboardController::class, 'teamPerformance']);
        Route::get('pipeline-overview', [DashboardController::class, 'pipelineOverview']);
        Route::get('ai-recommendations', [DashboardController::class, 'aiRecommendations']);
    });

    // Lead management routes
    Route::prefix('leads')->group(function () {
        Route::get('/', [LeadController::class, 'index']);
        Route::post('/', [LeadController::class, 'store']);
        Route::get('{lead}', [LeadController::class, 'show']);
        Route::put('{lead}', [LeadController::class, 'update']);
        Route::delete('{lead}', [LeadController::class, 'destroy']);
        Route::post('{lead}/status', [LeadController::class, 'updateStatus']);
        Route::post('{lead}/assign', [LeadController::class, 'assign']);
        Route::post('{lead}/score', [LeadController::class, 'recalculateScore']);
        Route::post('import', [LeadController::class, 'import']);
        Route::get('export', [LeadController::class, 'export']);
    });

    // Pipeline management routes
    Route::prefix('pipelines')->group(function () {
        Route::get('/', [PipelineController::class, 'index']);
        Route::post('/', [PipelineController::class, 'store']);
        Route::get('{pipeline}', [PipelineController::class, 'show']);
        Route::put('{pipeline}', [PipelineController::class, 'update']);
        Route::delete('{pipeline}', [PipelineController::class, 'destroy']);
        Route::get('{pipeline}/stages', [PipelineController::class, 'stages']);
        Route::post('{pipeline}/stages', [PipelineController::class, 'addStage']);
        Route::put('{pipeline}/stages/{stage}', [PipelineController::class, 'updateStage']);
        Route::delete('{pipeline}/stages/{stage}', [PipelineController::class, 'removeStage']);
    });

    // Pipeline view routes (Kanban)
    Route::prefix('pipeline-view')->group(function () {
        Route::get('{pipeline}', [PipelineController::class, 'pipelineView']);
        Route::put('lead/{lead}/move', [PipelineController::class, 'moveLead']);
    });

    // Task management routes
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('{task}', [TaskController::class, 'show']);
        Route::put('{task}', [TaskController::class, 'update']);
        Route::delete('{task}', [TaskController::class, 'destroy']);
        Route::post('{task}/complete', [TaskController::class, 'complete']);
        Route::get('statistics', [TaskController::class, 'statistics']);
        Route::get('overdue', [TaskController::class, 'overdue']);
        Route::get('due-today', [TaskController::class, 'dueToday']);
        Route::get('by-lead/{lead}', [TaskController::class, 'byLead']);
        Route::post('bulk-update-status', [TaskController::class, 'bulkUpdateStatus']);
        Route::post('{task}/reminders', [TaskController::class, 'setReminders']);
    });

    // Settings routes
    Route::prefix('settings')->group(function () {
        // Profile management
        Route::prefix('profile')->group(function () {
            Route::get('/', [SettingsController::class, 'profile']);
            Route::put('/', [SettingsController::class, 'updateProfile']);
            Route::put('password', [SettingsController::class, 'changePassword']);
            Route::put('notifications', [SettingsController::class, 'updateNotifications']);
        });

        // User management (Admin/Manager only)
        Route::prefix('users')->group(function () {
            Route::get('/', [SettingsController::class, 'users']);
            Route::post('/', [SettingsController::class, 'storeUser']);
            Route::get('{user}', [SettingsController::class, 'showUser']);
            Route::put('{user}', [SettingsController::class, 'updateUser']);
            Route::delete('{user}', [SettingsController::class, 'destroyUser']);
        });

        // Role and team management
        Route::get('roles', [SettingsController::class, 'roles']);
        Route::get('teams', [SettingsController::class, 'teams']);

        // Integration management
        Route::prefix('integrations')->group(function () {
            Route::get('/', [SettingsController::class, 'integrations']);
            Route::post('{service}/connect', [SettingsController::class, 'connectIntegration']);
            Route::delete('{service}/disconnect', [SettingsController::class, 'disconnectIntegration']);
            Route::get('{service}/status', [SettingsController::class, 'integrationStatus']);
        });

        // Data management
        Route::prefix('data')->group(function () {
            Route::post('import', [SettingsController::class, 'importData']);
            Route::get('export', [SettingsController::class, 'exportData']);
        });
    });

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::put('mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{notification}', [NotificationController::class, 'destroy']);
        Route::get('statistics', [NotificationController::class, 'statistics']);
    });

    // AI Insights routes
    Route::prefix('ai-insights')->group(function () {
        Route::get('/', [AiInsightController::class, 'index']);
        Route::get('leads/{lead}', [AiInsightController::class, 'leadInsights']);
        Route::get('global', [AiInsightController::class, 'globalInsights']);
        Route::put('{insight}/read', [AiInsightController::class, 'markAsRead']);
        Route::put('mark-all-as-read', [AiInsightController::class, 'markAllAsRead']);
        Route::get('statistics', [AiInsightController::class, 'statistics']);
        Route::post('generate/{lead}', [AiInsightController::class, 'generateInsights']);
    });
});
