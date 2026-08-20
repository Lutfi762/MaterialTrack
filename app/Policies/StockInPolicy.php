<?php

namespace App\Policies;

use App\Models\StockIn;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StockInPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['stock_ins.index']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StockIn $stockIn): bool
    {
        // Super admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Must have view permission
        if (! $user->hasAnyPermission(['stock_ins.index'])) {
            return false;
        }

        // Department user can only view their own transactions
        if ($user->hasRole('department_user')) {
            return $stockIn->user_id === $user->id;
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['stock_ins.create']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StockIn $stockIn): bool
    {
        // Must have update permission
        if (! $user->hasAnyPermission(['stock_ins.edit'])) {
            return false;
        }

        // Super admin can update all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Department user can only update their own transactions
        if ($user->hasRole('department_user')) {
            return $stockIn->user_id === $user->id;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StockIn $stockIn): bool
    {
        // Must have delete permission
        if (! $user->hasAnyPermission(['stock_ins.delete'])) {
            return false;
        }

        // Super admin can delete all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Department user can only delete their own transactions
        if ($user->hasRole('department_user')) {
            return $stockIn->user_id === $user->id;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StockIn $stockIn): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StockIn $stockIn): bool
    {
        return $user->hasRole('super_admin');
    }
}
