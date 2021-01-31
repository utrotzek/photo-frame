<?php

namespace App\Providers;

use App\Models\Index;
use App\Models\Queue;
use App\Observers\IndexObserver;
use App\Observers\QueueObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Index::observe(IndexObserver::class);
        Queue::observe(QueueObserver::class);
    }
}
