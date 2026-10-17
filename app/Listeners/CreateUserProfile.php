<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Support\Facades\Log;

class CreateUserProfile
{
    public function handle(UserRegistered $event): void
    {
        Log::info('Creating default profile for user: '.$event->user->id);
    }
}
