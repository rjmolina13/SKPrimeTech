<?php

namespace App\Policies;

use App\Models\Barangay;
use App\Models\User;

class BarangayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('municipal');
    }

    public function view(User $user, Barangay $barangay): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return true;
        }
        if ($user->hasRole('municipal')) {
            return $user->municipality_id === $barangay->municipality_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    public function update(User $user, Barangay $barangay): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    public function delete(User $user, Barangay $barangay): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }
}

