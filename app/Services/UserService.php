<?php

namespace App\Services;

use App\Contracts\UserRepository;
use App\Models\User;

class UserService
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {
    }

    public function getUserById(int $id): ?User
    {
        return $this->repository->findById($id);
    }

    public function isActive(int $id): bool
    {
        $user = $this->repository->findById($id);

        return $user !== null && $user->status === 'active';
    }

    public function updateUserProfile(int $id, array $data): bool
    {
        return $this->repository->updateProfile($id, $data);
    }
}
