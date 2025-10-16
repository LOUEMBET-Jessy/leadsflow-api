<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'account_id',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'settings',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
            'last_login_at' => 'datetime',
        ];
    }

    // Relations
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function assignedLeads()
    {
        return $this->belongsToMany(Lead::class, 'lead_assignments')
                    ->withPivot('assigned_at', 'assigned_by_user_id', 'notes')
                    ->withTimestamps();
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Méthodes utilitaires
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function isAdmin()
    {
        return $this->role === 'Admin';
    }

    public function isManager()
    {
        return in_array($this->role, ['Admin', 'Manager']);
    }

    public function canManageLeads()
    {
        return in_array($this->role, ['Admin', 'Manager', 'Commercial', 'GestLead']);
    }

    public function canViewReports()
    {
        return in_array($this->role, ['Admin', 'Manager', 'Marketing']);
    }

    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return $initials;
    }
}