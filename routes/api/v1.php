<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\PipelineController;
use App\Http\Controllers\Api\V1\StageController;
use App\Http\Controllers\Api\V1\InteractionController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\AutomationController;
use App\Http\Controllers\Api\V1\EmailSequenceController;
use App\Http\Controllers\Api\V1\SegmentController;
use App\Http\Controllers\Api\V1\IntegrationController;
use App\Http\Controllers\Api\V1\DashboardController;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Public lead capture routes
Route::prefix('leads')->group(function () {
    Route::post('capture', [LeadController::class, 'captureWebForm']);
});

// Protected routes (authentication required)
Route::middleware('auth.simple')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::put('password', [AuthController::class, 'changePassword']);
    });

    // Account routes
    Route::prefix('account')->group(function () {
        Route::get('/', [AccountController::class, 'show']);
        Route::put('/', [AccountController::class, 'update']);
        Route::get('stats', [AccountController::class, 'stats']);
        Route::get('users', [AccountController::class, 'users']);
        Route::post('users', [AccountController::class, 'createUser']);
        Route::put('users/{user}', [AccountController::class, 'updateUser']);
        Route::delete('users/{user}', [AccountController::class, 'deleteUser']);
    });

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('overview', [DashboardController::class, 'overview']);
        Route::get('leads-by-status', [DashboardController::class, 'leadsByStatus']);
        Route::get('leads-by-source', [DashboardController::class, 'leadsBySource']);
        Route::get('pipeline-funnel', [DashboardController::class, 'pipelineFunnel']);
        Route::get('recent-activities', [DashboardController::class, 'recentActivities']);
        Route::get('team-performance', [DashboardController::class, 'teamPerformance']);
        Route::get('conversion-rates', [DashboardController::class, 'conversionRates']);
        Route::get('monthly-trends', [DashboardController::class, 'monthlyTrends']);
        Route::get('top-sources', [DashboardController::class, 'topSources']);
        Route::get('overdue-tasks', [DashboardController::class, 'overdueTasks']);
    });

    // Lead routes
    Route::prefix('leads')->group(function () {
        Route::get('/', [LeadController::class, 'index']);
        Route::post('/', [LeadController::class, 'store']);
        Route::get('stats', [LeadController::class, 'stats']);
        Route::get('{lead}', [LeadController::class, 'show']);
        Route::put('{lead}', [LeadController::class, 'update']);
        Route::delete('{lead}', [LeadController::class, 'destroy']);
        Route::post('{lead}/assign', [LeadController::class, 'assign']);
        Route::delete('{lead}/unassign/{user}', [LeadController::class, 'unassign']);
        Route::put('{lead}/score', [LeadController::class, 'updateScore']);
    });

    // Pipeline routes
    Route::prefix('pipelines')->group(function () {
        Route::get('/', [PipelineController::class, 'index']);
        Route::post('/', [PipelineController::class, 'store']);
        Route::get('{pipeline}/stats', [PipelineController::class, 'stats']);
        Route::get('{pipeline}', [PipelineController::class, 'show']);
        Route::put('{pipeline}', [PipelineController::class, 'update']);
        Route::delete('{pipeline}', [PipelineController::class, 'destroy']);
        Route::post('reorder', [PipelineController::class, 'reorder']);
    });

    // Stage routes
    Route::prefix('pipelines/{pipeline}/stages')->group(function () {
        Route::get('/', [StageController::class, 'index']);
        Route::post('/', [StageController::class, 'store']);
        Route::post('reorder', [StageController::class, 'reorder']);
    });

    Route::prefix('stages')->group(function () {
        Route::get('{stage}', [StageController::class, 'show']);
        Route::put('{stage}', [StageController::class, 'update']);
        Route::delete('{stage}', [StageController::class, 'destroy']);
        Route::get('{stage}/stats', [StageController::class, 'stats']);
        Route::post('{stage}/move-lead', [StageController::class, 'moveLead']);
    });

    // Interaction routes
    Route::prefix('interactions')->group(function () {
        Route::get('/', [InteractionController::class, 'all']);
        Route::get('recent', [InteractionController::class, 'recent']);
        Route::get('{interaction}', [InteractionController::class, 'show']);
        Route::put('{interaction}', [InteractionController::class, 'update']);
        Route::delete('{interaction}', [InteractionController::class, 'destroy']);
    });

    Route::prefix('leads/{lead}/interactions')->group(function () {
        Route::get('/', [InteractionController::class, 'index']);
        Route::post('/', [InteractionController::class, 'store']);
        Route::get('stats', [InteractionController::class, 'stats']);
    });

    // Task routes
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('my-tasks', [TaskController::class, 'myTasks']);
        Route::get('overdue', [TaskController::class, 'overdue']);
        Route::get('due-today', [TaskController::class, 'dueToday']);
        Route::get('stats', [TaskController::class, 'stats']);
        Route::get('{task}', [TaskController::class, 'show']);
        Route::put('{task}', [TaskController::class, 'update']);
        Route::delete('{task}', [TaskController::class, 'destroy']);
        Route::post('{task}/complete', [TaskController::class, 'complete']);
    });

    // Automation routes
    Route::prefix('automations')->group(function () {
        Route::get('/', [AutomationController::class, 'index']);
        Route::post('/', [AutomationController::class, 'store']);
        Route::get('trigger-types', [AutomationController::class, 'triggerTypes']);
        Route::get('action-types', [AutomationController::class, 'actionTypes']);
        Route::get('stats', [AutomationController::class, 'stats']);
        Route::get('{automation}', [AutomationController::class, 'show']);
        Route::put('{automation}', [AutomationController::class, 'update']);
        Route::delete('{automation}', [AutomationController::class, 'destroy']);
        Route::post('{automation}/toggle', [AutomationController::class, 'toggle']);
        Route::post('{automation}/test/{lead}', [AutomationController::class, 'test']);
        Route::post('{automation}/execute/{lead}', [AutomationController::class, 'execute']);
    });

    // Email sequence routes
    Route::prefix('email-sequences')->group(function () {
        Route::get('/', [EmailSequenceController::class, 'index']);
        Route::post('/', [EmailSequenceController::class, 'store']);
        Route::get('personalization-tags', [EmailSequenceController::class, 'personalizationTags']);
        Route::get('{sequence}', [EmailSequenceController::class, 'show']);
        Route::put('{sequence}', [EmailSequenceController::class, 'update']);
        Route::delete('{sequence}', [EmailSequenceController::class, 'destroy']);
        Route::get('{sequence}/stats', [EmailSequenceController::class, 'stats']);
        Route::post('{sequence}/enroll/{lead}', [EmailSequenceController::class, 'enrollLead']);
        Route::post('{sequence}/steps', [EmailSequenceController::class, 'addStep']);
        Route::post('{sequence}/steps/reorder', [EmailSequenceController::class, 'reorderSteps']);
    });

    Route::prefix('sequence-steps')->group(function () {
        Route::put('{step}', [EmailSequenceController::class, 'updateStep']);
        Route::delete('{step}', [EmailSequenceController::class, 'deleteStep']);
    });

    // Segment routes
    Route::prefix('segments')->group(function () {
        Route::get('/', [SegmentController::class, 'index']);
        Route::post('/', [SegmentController::class, 'store']);
        Route::get('field-operators', [SegmentController::class, 'fieldOperators']);
        Route::get('available-fields', [SegmentController::class, 'availableFields']);
        Route::get('stats', [SegmentController::class, 'stats']);
        Route::get('{segment}', [SegmentController::class, 'show']);
        Route::put('{segment}', [SegmentController::class, 'update']);
        Route::delete('{segment}', [SegmentController::class, 'destroy']);
        Route::get('{segment}/leads', [SegmentController::class, 'leads']);
        Route::post('{segment}/test/{lead}', [SegmentController::class, 'testLead']);
        Route::post('{segment}/update-count', [SegmentController::class, 'updateCount']);
    });

    // Integration routes
    Route::prefix('integrations')->group(function () {
        Route::get('/', [IntegrationController::class, 'index']);
        Route::post('/', [IntegrationController::class, 'store']);
        Route::get('types', [IntegrationController::class, 'types']);
        Route::get('providers', [IntegrationController::class, 'providers']);
        Route::get('config-template', [IntegrationController::class, 'configTemplate']);
        Route::get('stats', [IntegrationController::class, 'stats']);
        Route::get('{integration}', [IntegrationController::class, 'show']);
        Route::put('{integration}', [IntegrationController::class, 'update']);
        Route::delete('{integration}', [IntegrationController::class, 'destroy']);
        Route::post('{integration}/test', [IntegrationController::class, 'test']);
        Route::post('{integration}/sync', [IntegrationController::class, 'sync']);
    });
});