<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    /**
     * Get all integrations for the account
     */
    public function index(Request $request): JsonResponse
    {
        $integrations = Integration::where('account_id', $request->user()->account_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['integrations' => $integrations]);
    }

    /**
     * Get a specific integration
     */
    public function show(Request $request, Integration $integration): JsonResponse
    {
        // Vérifier que l'intégration appartient au compte
        if ($integration->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Integration not found'], 404);
        }

        return response()->json(['integration' => $integration]);
    }

    /**
     * Create a new integration
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:CRM,Calendrier,API,Email,Social',
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:100',
            'config' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $integration = Integration::create([
            'account_id' => $request->user()->account_id,
            'type' => $request->type,
            'name' => $request->name,
            'provider' => $request->provider,
            'config' => $request->config,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Integration created successfully',
            'integration' => $integration,
        ], 201);
    }

    /**
     * Update an integration
     */
    public function update(Request $request, Integration $integration): JsonResponse
    {
        // Vérifier que l'intégration appartient au compte
        if ($integration->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Integration not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'config' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $integration->update($request->only(['name', 'config', 'is_active']));

        return response()->json([
            'message' => 'Integration updated successfully',
            'integration' => $integration,
        ]);
    }

    /**
     * Delete an integration
     */
    public function destroy(Request $request, Integration $integration): JsonResponse
    {
        // Vérifier que l'intégration appartient au compte
        if ($integration->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Integration not found'], 404);
        }

        $integration->delete();

        return response()->json([
            'message' => 'Integration deleted successfully',
        ]);
    }

    /**
     * Test integration connection
     */
    public function test(Request $request, Integration $integration): JsonResponse
    {
        // Vérifier que l'intégration appartient au compte
        if ($integration->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Integration not found'], 404);
        }

        try {
            $result = $integration->sync();
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Connection test successful' : 'Connection test failed',
                'integration' => $integration,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'integration' => $integration,
            ], 500);
        }
    }

    /**
     * Sync integration data
     */
    public function sync(Request $request, Integration $integration): JsonResponse
    {
        // Vérifier que l'intégration appartient au compte
        if ($integration->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Integration not found'], 404);
        }

        try {
            $result = $integration->sync();
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Sync completed successfully' : 'Sync failed',
                'integration' => $integration,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
                'integration' => $integration,
            ], 500);
        }
    }

    /**
     * Get integration statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;

        $stats = [
            'total' => Integration::where('account_id', $accountId)->count(),
            'active' => Integration::where('account_id', $accountId)
                ->where('is_active', true)
                ->count(),
            'connected' => Integration::where('account_id', $accountId)
                ->where('is_active', true)
                ->whereNotNull('config->access_token')
                ->count(),
            'by_type' => Integration::where('account_id', $accountId)
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type'),
            'by_provider' => Integration::where('account_id', $accountId)
                ->selectRaw('provider, count(*) as count')
                ->groupBy('provider')
                ->get()
                ->pluck('count', 'provider'),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Get available integration types
     */
    public function types(): JsonResponse
    {
        $types = [
            'CRM' => 'CRM',
            'Calendrier' => 'Calendrier',
            'API' => 'API',
            'Email' => 'Email',
            'Social' => 'Réseaux sociaux',
        ];

        return response()->json(['types' => $types]);
    }

    /**
     * Get available providers
     */
    public function providers(): JsonResponse
    {
        $providers = [
            'salesforce' => 'Salesforce',
            'hubspot' => 'HubSpot',
            'pipedrive' => 'Pipedrive',
            'google' => 'Google',
            'microsoft' => 'Microsoft',
            'zapier' => 'Zapier',
            'webhook' => 'Webhook',
        ];

        return response()->json(['providers' => $providers]);
    }

    /**
     * Get integration configuration template
     */
    public function configTemplate(Request $request): JsonResponse
    {
        $provider = $request->get('provider');
        $type = $request->get('type');

        $templates = [
            'salesforce' => [
                'client_id' => 'string',
                'client_secret' => 'string',
                'access_token' => 'string',
                'refresh_token' => 'string',
                'instance_url' => 'string',
                'sync_interval' => 'integer',
            ],
            'hubspot' => [
                'api_key' => 'string',
                'sync_interval' => 'integer',
            ],
            'google' => [
                'client_id' => 'string',
                'client_secret' => 'string',
                'access_token' => 'string',
                'refresh_token' => 'string',
                'sync_interval' => 'integer',
            ],
            'webhook' => [
                'url' => 'string',
                'secret' => 'string',
                'events' => 'array',
            ],
        ];

        $template = $templates[$provider] ?? [];

        return response()->json(['template' => $template]);
    }
}
