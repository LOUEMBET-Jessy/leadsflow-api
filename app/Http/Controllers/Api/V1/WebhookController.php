<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhook
     */
    public function handle(Request $request, string $endpoint): JsonResponse
    {
        try {
            $webhook = WebhookEndpoint::where('name', $endpoint)
                ->where('is_active', true)
                ->first();

            if (!$webhook) {
                return response()->json(['error' => 'Webhook endpoint not found'], 404);
            }

            // Verify webhook signature if provided
            if ($request->hasHeader('X-Webhook-Signature')) {
                $signature = $request->header('X-Webhook-Signature');
                $payload = $request->getContent();
                $expectedSignature = hash_hmac('sha256', $payload, $webhook->secret_key);

                if (!hash_equals($expectedSignature, $signature)) {
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            }

            // Process webhook data
            $this->processWebhookData($webhook, $request->all());

            Log::info('Webhook processed successfully', [
                'endpoint' => $endpoint,
                'data' => $request->all()
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Process webhook data based on endpoint configuration
     */
    protected function processWebhookData(WebhookEndpoint $webhook, array $data): void
    {
        $events = $webhook->events;

        foreach ($events as $event) {
            switch ($event) {
                case 'lead.created':
                    $this->handleLeadCreated($data);
                    break;
                case 'lead.updated':
                    $this->handleLeadUpdated($data);
                    break;
                case 'lead.deleted':
                    $this->handleLeadDeleted($data);
                    break;
                case 'task.created':
                    $this->handleTaskCreated($data);
                    break;
                case 'task.completed':
                    $this->handleTaskCompleted($data);
                    break;
                default:
                    Log::info('Unknown webhook event', ['event' => $event, 'data' => $data]);
            }
        }
    }

    /**
     * Handle lead created webhook
     */
    protected function handleLeadCreated(array $data): void
    {
        // Process lead creation from external system
        Log::info('Processing lead created webhook', $data);
    }

    /**
     * Handle lead updated webhook
     */
    protected function handleLeadUpdated(array $data): void
    {
        // Process lead update from external system
        Log::info('Processing lead updated webhook', $data);
    }

    /**
     * Handle lead deleted webhook
     */
    protected function handleLeadDeleted(array $data): void
    {
        // Process lead deletion from external system
        Log::info('Processing lead deleted webhook', $data);
    }

    /**
     * Handle task created webhook
     */
    protected function handleTaskCreated(array $data): void
    {
        // Process task creation from external system
        Log::info('Processing task created webhook', $data);
    }

    /**
     * Handle task completed webhook
     */
    protected function handleTaskCompleted(array $data): void
    {
        // Process task completion from external system
        Log::info('Processing task completed webhook', $data);
    }
}
