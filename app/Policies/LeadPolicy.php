<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeadPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('leads.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lead $lead): bool
    {
        return $user->can('leads.view') && (
            $user->id === $lead->assigned_to_user_id ||
            $user->id === $lead->created_by_user_id ||
            $user->hasRole(['admin', 'manager'])
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('leads.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lead $lead): bool
    {
        return $user->can('leads.edit') && (
            $user->id === $lead->assigned_to_user_id ||
            $user->id === $lead->created_by_user_id ||
            $user->hasRole(['admin', 'manager'])
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lead $lead): bool
    {
        return $user->can('leads.delete') && (
            $user->id === $lead->created_by_user_id ||
            $user->hasRole(['admin'])
        );
    }

    /**
     * Determine whether the user can assign the lead.
     */
    public function assign(User $user, Lead $lead): bool
    {
        return $user->can('leads.assign') && $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can export leads.
     */
    public function export(User $user): bool
    {
        return $user->can('leads.export');
    }

    /**
     * Determine whether the user can import leads.
     */
    public function import(User $user): bool
    {
        return $user->can('leads.import');
    }
}
