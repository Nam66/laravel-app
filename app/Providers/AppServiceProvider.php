<?php

namespace App\Providers;

use App\Contracts\MailerInterface;
use App\Contracts\PaymentGateway;
use App\Services\MomoPaymentGateway;
use App\Services\SendGridMailer;
use App\Services\SmtpMailer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, MomoPaymentGateway::class);

        $this->app->bind(MailerInterface::class, function () {
            return match (config('mail.default', 'smtp')) {
                'smtp' => new SmtpMailer(),
                'sendgrid' => new SendGridMailer(),
                default => throw new \RuntimeException('Unsupported mail driver.'),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
