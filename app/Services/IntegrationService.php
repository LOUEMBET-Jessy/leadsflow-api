<?php

namespace App\Services;

use App\Models\Integration;
use App\Models\User;
use App\Models\Lead;
use App\Models\Interaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class IntegrationService
{
    /**
     * Connect to external service
     */
    public function connectService(User $user, string $service, array $credentials): Integration
    {
        $integration = Integration::updateOrCreate(
            [
                'user_id' => $user->id,
                'service_name' => $service,
            ],
            [
                'access_token' => Crypt::encrypt($credentials['access_token']),
                'refresh_token' => isset($credentials['refresh_token']) ? Crypt::encrypt($credentials['refresh_token']) : null,
                'expires_at' => $credentials['expires_at'] ?? null,
                'status' => 'connected',
                'settings' => $credentials['settings'] ?? [],
            ]
        );

        return $integration;
    }

    /**
     * Disconnect from external service
     */
    public function disconnectService(User $user, string $service): bool
    {
        $integration = Integration::where('user_id', $user->id)
            ->where('service_name', $service)
            ->first();

        if ($integration) {
            $integration->update(['status' => 'disconnected']);
            return true;
        }

        return false;
    }

    /**
     * Sync leads from external CRM
     */
    public function syncLeadsFromCrm(Integration $integration): array
    {
        try {
            $accessToken = Crypt::decrypt($integration->access_token);
            $leads = $this->fetchLeadsFromCrm($integration->service_name, $accessToken);
            
            $synced = [];
            foreach ($leads as $leadData) {
                $lead = $this->createOrUpdateLead($leadData, $integration->user_id);
                $synced[] = $lead;
            }

            return $synced;
        } catch (\Exception $e) {
            Log::error('CRM sync failed', [
                'integration_id' => $integration->id,
                'service' => $integration->service_name,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Sync emails from external service
     */
    public function syncEmailsFromService(Integration $integration): array
    {
        try {
            $accessToken = Crypt::decrypt($integration->access_token);
            $emails = $this->fetchEmailsFromService($integration->service_name, $accessToken);
            
            $synced = [];
            foreach ($emails as $emailData) {
                $interaction = $this->createEmailInteraction($emailData, $integration->user_id);
                $synced[] = $interaction;
            }

            return $synced;
        } catch (\Exception $e) {
            Log::error('Email sync failed', [
                'integration_id' => $integration->id,
                'service' => $integration->service_name,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Sync calendar events
     */
    public function syncCalendarEvents(Integration $integration): array
    {
        try {
            $accessToken = Crypt::decrypt($integration->access_token);
            $events = $this->fetchCalendarEvents($integration->service_name, $accessToken);
            
            $synced = [];
            foreach ($events as $eventData) {
                $task = $this->createTaskFromEvent($eventData, $integration->user_id);
                $synced[] = $task;
            }

            return $synced;
        } catch (\Exception $e) {
            Log::error('Calendar sync failed', [
                'integration_id' => $integration->id,
                'service' => $integration->service_name,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Send lead to external CRM
     */
    public function sendLeadToCrm(Lead $lead, Integration $integration): bool
    {
        try {
            $accessToken = Crypt::decrypt($integration->access_token);
            $leadData = $this->formatLeadForCrm($lead, $integration->service_name);
            
            $response = $this->sendToCrm($integration->service_name, $accessToken, $leadData);
            
            if ($response['success']) {
                // Update lead with external ID
                $lead->update(['external_id' => $response['id']]);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Send lead to CRM failed', [
                'lead_id' => $lead->id,
                'integration_id' => $integration->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(Integration $integration): bool
    {
        try {
            $refreshToken = Crypt::decrypt($integration->refresh_token);
            $newTokens = $this->refreshTokens($integration->service_name, $refreshToken);
            
            $integration->update([
                'access_token' => Crypt::encrypt($newTokens['access_token']),
                'refresh_token' => isset($newTokens['refresh_token']) ? Crypt::encrypt($newTokens['refresh_token']) : $integration->refresh_token,
                'expires_at' => $newTokens['expires_at'] ?? $integration->expires_at,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'integration_id' => $integration->id,
                'service' => $integration->service_name,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Check integration health
     */
    public function checkIntegrationHealth(Integration $integration): array
    {
        try {
            $accessToken = Crypt::decrypt($integration->access_token);
            $isValid = $this->validateToken($integration->service_name, $accessToken);
            
            if (!$isValid && $integration->refresh_token) {
                $this->refreshAccessToken($integration);
                $isValid = true;
            }
            
            return [
                'status' => $isValid ? 'healthy' : 'unhealthy',
                'last_check' => now(),
                'expires_at' => $integration->expires_at,
                'is_expired' => $integration->isExpired(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'last_check' => now(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch leads from CRM
     */
    protected function fetchLeadsFromCrm(string $service, string $accessToken): array
    {
        // Mock implementation - in production, integrate with actual CRM APIs
        return [
            [
                'external_id' => 'crm_123',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'company' => 'Example Corp',
                'title' => 'CEO',
                'phone' => '+1234567890',
            ]
        ];
    }

    /**
     * Fetch emails from service
     */
    protected function fetchEmailsFromService(string $service, string $accessToken): array
    {
        // Mock implementation - in production, integrate with Gmail/Outlook APIs
        return [
            [
                'subject' => 'Meeting Request',
                'from' => 'client@example.com',
                'to' => 'sales@company.com',
                'body' => 'Let\'s schedule a meeting to discuss the proposal.',
                'date' => now()->subHours(2),
            ]
        ];
    }

    /**
     * Fetch calendar events
     */
    protected function fetchCalendarEvents(string $service, string $accessToken): array
    {
        // Mock implementation - in production, integrate with Google Calendar/Outlook APIs
        return [
            [
                'title' => 'Client Meeting',
                'start' => now()->addDays(1)->setTime(10, 0),
                'end' => now()->addDays(1)->setTime(11, 0),
                'description' => 'Meeting with potential client',
            ]
        ];
    }

    /**
     * Create or update lead from external data
     */
    protected function createOrUpdateLead(array $data, int $userId): Lead
    {
        return Lead::updateOrCreate(
            ['external_id' => $data['external_id']],
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'company' => $data['company'] ?? null,
                'title' => $data['title'] ?? null,
                'phone' => $data['phone'] ?? null,
                'source' => 'CRM Import',
                'created_by_user_id' => $userId,
            ]
        );
    }

    /**
     * Create email interaction
     */
    protected function createEmailInteraction(array $data, int $userId): Interaction
    {
        $lead = Lead::where('email', $data['from'])->first();
        
        if (!$lead) {
            // Create lead from email
            $lead = Lead::create([
                'first_name' => explode(' ', $data['from'])[0] ?? 'Unknown',
                'last_name' => explode(' ', $data['from'])[1] ?? '',
                'email' => $data['from'],
                'source' => 'Email Import',
                'created_by_user_id' => $userId,
            ]);
        }

        return Interaction::create([
            'lead_id' => $lead->id,
            'user_id' => $userId,
            'type' => 'email_received',
            'summary' => $data['subject'],
            'details' => $data['body'],
            'interaction_date' => $data['date'],
        ]);
    }

    /**
     * Create task from calendar event
     */
    protected function createTaskFromEvent(array $data, int $userId): \App\Models\Task
    {
        return \App\Models\Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'due_date' => $data['start'],
            'priority' => 'medium',
            'assigned_to_user_id' => $userId,
            'created_by_user_id' => $userId,
        ]);
    }

    /**
     * Format lead for CRM
     */
    protected function formatLeadForCrm(Lead $lead, string $service): array
    {
        return [
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email' => $lead->email,
            'company' => $lead->company,
            'title' => $lead->title,
            'phone' => $lead->phone,
            'status' => $lead->status->name,
        ];
    }

    /**
     * Send data to CRM
     */
    protected function sendToCrm(string $service, string $accessToken, array $data): array
    {
        // Mock implementation - in production, make actual API calls
        return [
            'success' => true,
            'id' => 'crm_' . uniqid(),
        ];
    }

    /**
     * Refresh tokens
     */
    protected function refreshTokens(string $service, string $refreshToken): array
    {
        // Mock implementation - in production, make actual API calls
        return [
            'access_token' => 'new_access_token_' . uniqid(),
            'refresh_token' => 'new_refresh_token_' . uniqid(),
            'expires_at' => now()->addHours(1),
        ];
    }

    /**
     * Validate token
     */
    protected function validateToken(string $service, string $accessToken): bool
    {
        // Mock implementation - in production, validate with actual API
        return true;
    }
}
