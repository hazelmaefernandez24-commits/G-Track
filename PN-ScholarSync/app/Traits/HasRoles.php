<?php

namespace App\Traits;

trait HasRoles
{
    /**
     * Check if the user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->user_role === $role;
    }

    /**
     * Check if the user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->user_role, $roles);
    }

    /**
     * Check if the user has all of the given roles
     */
    public function hasAllRoles(array $roles): bool
    {
        return count($roles) === 1 && $this->user_role === $roles[0];
    }
}
