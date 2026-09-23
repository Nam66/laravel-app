<?php

namespace Tests\Unit;

use App\Contracts\MailerInterface;
use App\Services\SendGridMailer;
use App\Services\SmtpMailer;
use Tests\TestCase;

class MailerContainerTest extends TestCase
{
    public function test_it_resolves_smtp_mailer_from_container(): void
    {
        config()->set('mail.default', 'smtp');

        $mailer = app()->make(MailerInterface::class);

        $this->assertInstanceOf(SmtpMailer::class, $mailer);
    }

    public function test_it_resolves_sendgrid_mailer_from_container(): void
    {
        config()->set('mail.default', 'sendgrid');

        $mailer = app()->make(MailerInterface::class);

        $this->assertInstanceOf(SendGridMailer::class, $mailer);
    }
}
