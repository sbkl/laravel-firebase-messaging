<?php

namespace Sbkl\LaravelFirebaseMessaging;

use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            $this->publishes([
                __DIR__.'/../config/firebase.php' => config_path('firebase.php'),
            ], 'config');

            if (! class_exists('CreateFirebaseChannelsTable') && ! class_exists('CreateFirebaseSubscriptionsTable')) {
                $this->publishes([
                    __DIR__.'/../database/migrations/create_firebase_channels_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_firebase_channels_table.php'),
                    __DIR__.'/../database/migrations/create_firebase_subscriptions_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_firebase_subscriptions_table.php'),
                    __DIR__.'/../database/migrations/create_firebase_notifications_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_firebase_notifications_table.php'),
                ], 'migrations');
            }
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/firebase.php',
            'firebase'
        );
        $this->app->bind('firebase', function () {
            return Firebase::databaseSetup();
        });
    }
}
