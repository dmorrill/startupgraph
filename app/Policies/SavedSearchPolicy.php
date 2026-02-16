<?php

namespace App\Policies;

use App\Models\SavedSearch;
use App\Models\User;

class SavedSearchPolicy
{
    public function update(User $user, SavedSearch $savedSearch): bool
    {
        return $user->id === $savedSearch->user_id;
    }

    public function delete(User $user, SavedSearch $savedSearch): bool
    {
        return $user->id === $savedSearch->user_id;
    }
}
