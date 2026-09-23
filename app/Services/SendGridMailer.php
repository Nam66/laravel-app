<?php

namespace App\Services;

use App\Contracts\MailerInterface;

class SendGridMailer implements MailerInterface
{
    public function send(string $to, string $subject, string $body): void
    {
        // Thay bằng logic SendGrid thực tế nếu cần.
    }
}
