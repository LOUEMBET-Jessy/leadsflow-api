<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'team_id',
        'two_factor_secret',
        'current_team_id',
        'profile_photo_path',
        'settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the team that owns the user.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the current team that the user belongs to.
     */
    public function currentTeam()
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * Get the leads assigned to the user.
     */
    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_to_user_id');
    }

    /**
     * Get the leads created by the user.
     */
    public function createdLeads()
    {
        return $this->hasMany(Lead::class, 'created_by_user_id');
    }

    /**
     * Get the tasks assigned to the user.
     */
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to_user_id');
    }

    /**
     * Get the tasks created by the user.
     */
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by_user_id');
    }

    /**
     * Get the interactions created by the user.
     */
    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    /**
     * Get the integrations for the user.
     */
    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    /**
     * Get the AI insights for the user.
     */
    public function aiInsights()
    {
        return $this->hasMany(AiInsight::class);
    }

    /**
     * Get the automations created by the user.
     */
    public function automations()
    {
        return $this->hasMany(Automation::class, 'created_by_user_id');
    }

    /**
     * Get the webhook endpoints created by the user.
     */
    public function webhookEndpoints()
    {
        return $this->hasMany(WebhookEndpoint::class, 'created_by_user_id');
    }
}
