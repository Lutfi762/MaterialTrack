<?php

namespace App\Policies;

use App\Models\StockOut;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StockOutPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StockOut $stockOut): bool
    {
        // Super admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Department user can only view transactions from their department
        if ($user->hasRole('department_user')) {
            return $stockOut->department_id === $user->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StockOut $stockOut): bool
    {
        // Super admin can update all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Department user can only update transactions from their department
        if ($user->hasRole('department_user')) {
            return $stockOut->department_id === $user->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StockOut $stockOut): bool
    {
        // Super admin can delete all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Department user can only delete transactions from their department
        if ($user->hasRole('department_user')) {
            return $stockOut->department_id === $user->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StockOut $stockOut): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StockOut $stockOut): bool
    {
        return $user->hasRole('super_admin');
    }
}
