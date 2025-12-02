<?php

namespace App\Providers;

use App\Services\Sms\FakeSmsGateway;
use App\Services\Sms\SmppSmsGateway;
use App\Services\Sms\SmsGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsGateway::class, FakeSmsGateway::class);
        $this->app->bind(SmsGateway::class, SmppSmsGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
