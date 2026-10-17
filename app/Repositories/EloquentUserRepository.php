<?php

namespace App\Repositories;

use App\Contracts\UserRepository;
use App\Models\User;

class EloquentUserRepository implements UserRepository
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $user = User::find($id);

        if (! $user) {
            return false;
        }

        $user->status = $status;

        return $user->save();
    }

    public function updateProfile(int $id, array $data): bool
    {
        $user = User::find($id);

        if (! $user) {
            return false;
        }

        return $user->update($data);
    }
}
