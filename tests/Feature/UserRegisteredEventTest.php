<?php

namespace Tests\Feature;

use App\Events\UserRegistered;
use App\Listeners\CreateUserProfile;
use App\Listeners\SendWelcomeEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserRegisteredEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_user_registered_event_on_registration(): void
    {
        Event::fake();

        $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'secret123',
        ])->assertCreated();

        Event::assertDispatched(UserRegistered::class);
    }

    public function test_it_runs_welcome_email_and_profile_listeners(): void
    {
        Event::fake([UserRegistered::class => [SendWelcomeEmail::class, CreateUserProfile::class]]);

        event(new UserRegistered(
            new \App\Models\User([
                'name' => 'Alice',
                'email' => 'alice@example.com',
            ])
        ));

        Event::assertDispatched(UserRegistered::class, function ($event) {
            return $event->user->email === 'alice@example.com';
        });
    }
}
