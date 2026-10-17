<?php

namespace App\Contracts;

use App\Models\User;

interface UserRepository
{
    public function findById(int $id): ?User;

    public function updateStatus(int $id, string $status): bool;

    public function updateProfile(int $id, array $data): bool;
}
