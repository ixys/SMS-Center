<?php

namespace App\Providers;

use App\Services\Sms\FakeSmsGateway;
use App\Services\Sms\SmsGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsGateway::class, function () {
            // En prod tu remplacerais FakeSmsGateway par ton vrai gateway SMPP/Twilio/etc.
            return new FakeSmsGateway();
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
