<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        Log::info('Sending welcome email to: '.$event->user->email);
    }
}
