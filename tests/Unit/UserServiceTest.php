<?php

namespace Tests\Unit;

use App\Contracts\UserRepository;
use App\Models\User;
use App\Services\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function test_it_returns_user_from_repository(): void
    {
        $user = new User(['id' => 1, 'name' => 'Alice', 'status' => 'active']);

        $repository = new class($user) implements UserRepository {
            public function __construct(private readonly User $user)
            {
            }

            public function findById(int $id): ?User
            {
                return $this->user;
            }

            public function updateStatus(int $id, string $status): bool
            {
                return true;
            }

            public function updateProfile(int $id, array $data): bool
            {
                return true;
            }
        };

        $service = new UserService($repository);

        $this->assertSame($user, $service->getUserById(1));
        $this->assertTrue($service->isActive(1));
    }

    public function test_it_updates_profile_via_repository(): void
    {
        $repository = new class implements UserRepository {
            public function findById(int $id): ?User
            {
                return null;
            }

            public function updateStatus(int $id, string $status): bool
            {
                return true;
            }

            public function updateProfile(int $id, array $data): bool
            {
                return true;
            }
        };

        $service = new UserService($repository);

        $this->assertTrue($service->updateUserProfile(1, ['name' => 'Bob']));
    }
}
