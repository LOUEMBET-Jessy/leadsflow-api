<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'type',
        'name',
        'provider',
        'config',
        'is_active',
        'last_sync_at',
        'sync_status',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'sync_status' => 'array',
    ];

    // Relations
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    // Méthodes utilitaires
    public function isConnected()
    {
        return $this->is_active && !empty($this->config['access_token'] ?? null);
    }

    public function needsSync()
    {
        if (!$this->is_active) return false;

        $lastSync = $this->last_sync_at;
        if (!$lastSync) return true;

        $syncInterval = $this->config['sync_interval'] ?? 3600; // 1 heure par défaut
        return $lastSync->addSeconds($syncInterval)->isPast();
    }

    public function getStatusColorAttribute()
    {
        if (!$this->is_active) return '#95a5a6';
        if (!$this->isConnected()) return '#e74c3c';
        if ($this->needsSync()) return '#f39c12';
        return '#27ae60';
    }

    public function getStatusTextAttribute()
    {
        if (!$this->is_active) return 'Inactif';
        if (!$this->isConnected()) return 'Non connecté';
        if ($this->needsSync()) return 'Synchronisation nécessaire';
        return 'Connecté';
    }

    public function getLastSyncTextAttribute()
    {
        if (!$this->last_sync_at) return 'Jamais synchronisé';
        return $this->last_sync_at->diffForHumans();
    }

    public function updateSyncStatus($status, $message = null, $data = null)
    {
        $this->update([
            'last_sync_at' => now(),
            'sync_status' => [
                'status' => $status,
                'message' => $message,
                'data' => $data,
                'updated_at' => now()->toISOString(),
            ]
        ]);
    }

    public function getConfigValue($key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    public function setConfigValue($key, $value)
    {
        $config = $this->config;
        data_set($config, $key, $value);
        $this->update(['config' => $config]);
    }

    public function getProviderIconAttribute()
    {
        return match($this->provider) {
            'salesforce' => '🔵',
            'hubspot' => '🟠',
            'pipedrive' => '🟣',
            'google' => '🔴',
            'microsoft' => '🔵',
            'zapier' => '⚡',
            'webhook' => '🔗',
            default => '🔌'
        };
    }

    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'CRM' => '📊',
            'Calendrier' => '📅',
            'API' => '🔌',
            'Email' => '📧',
            'Social' => '📱',
            default => '🔗'
        };
    }

    // Méthodes de synchronisation (à implémenter selon le provider)
    public function sync()
    {
        if (!$this->isConnected()) {
            $this->updateSyncStatus('error', 'Intégration non connectée');
            return false;
        }

        try {
            $this->updateSyncStatus('syncing', 'Synchronisation en cours...');

            // Logique de synchronisation spécifique au provider
            $result = $this->performSync();

            if ($result['success']) {
                $this->updateSyncStatus('success', 'Synchronisation réussie', $result['data']);
                return true;
            } else {
                $this->updateSyncStatus('error', $result['message'], $result['data']);
                return false;
            }
        } catch (\Exception $e) {
            $this->updateSyncStatus('error', 'Erreur lors de la synchronisation: ' . $e->getMessage());
            return false;
        }
    }

    protected function performSync()
    {
        // Cette méthode doit être implémentée selon le provider
        // Exemple pour Salesforce, HubSpot, etc.
        
        return match($this->provider) {
            'salesforce' => $this->syncSalesforce(),
            'hubspot' => $this->syncHubSpot(),
            'google' => $this->syncGoogle(),
            'microsoft' => $this->syncMicrosoft(),
            default => ['success' => false, 'message' => 'Provider non supporté']
        };
    }

    protected function syncSalesforce()
    {
        // Implémentation Salesforce
        return ['success' => true, 'data' => ['synced_records' => 0]];
    }

    protected function syncHubSpot()
    {
        // Implémentation HubSpot
        return ['success' => true, 'data' => ['synced_records' => 0]];
    }

    protected function syncGoogle()
    {
        // Implémentation Google
        return ['success' => true, 'data' => ['synced_records' => 0]];
    }

    protected function syncMicrosoft()
    {
        // Implémentation Microsoft
        return ['success' => true, 'data' => ['synced_records' => 0]];
    }
}