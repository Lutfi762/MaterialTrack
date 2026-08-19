<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Item $item): bool
    {
        // Admin can view all items
        if ($user->hasRole('admin')) {
            return true;
        }

        // Department user can view items from their assigned categories
        if ($user->hasRole('department_user')) {
            return $item->category?->departments->contains('id', $user->department_id) ?? false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['items.create']);
    }

    public function update(User $user, Item $item): bool
    {
        // Admin can update all items
        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Item $item): bool
    {
        // Admin can delete all items
        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    public function restore(User $user, Item $item): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Item $item): bool
    {
        return $user->hasRole('admin');
    }
}
