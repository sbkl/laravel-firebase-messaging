<?php

namespace Sbkl\LaravelFirebaseMessaging\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Sbkl\LaravelFirebaseMessaging\FirebaseServiceProvider;
use Sbkl\LaravelFirebaseMessaging\Facades\Firebase;

class LaravelTestCase extends TestCase
{
    public $token;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        $this->artisan('migrate');

        // $this->withFactories(__DIR__.'/../database/factories');

        $this->token = 'fYwH68NsJU5JoIhjohm1mk:APA91bHDgmCRDFU3KKfUG1oxixm9sXHXd9L335gjyt3zFVNDoDh_5sLj_4_fWW3oV3PO52StTRHvVQjrTmhxmEm3c_KBadjR2J4bC-E_7a4wUu72nOSqfyO-OZb9IQtTlQlcnybowj8Q';
    }

    protected function getPackageProviders($app)
    {
        return [
            FirebaseServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Firebase' => Firebase::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        include_once __DIR__.'/../database/migrations/create_firebase_channels_table.php.stub';
        include_once __DIR__.'/../database/migrations/create_firebase_subscriptions_table.php.stub';
        include_once __DIR__.'/../database/migrations/create_firebase_notifications_table.php.stub';
        (new \CreateFirebaseChannelsTable)->up();
        (new \CreateFirebaseSubscriptionsTable)->up();
        (new \CreateFirebaseNotificationsTable)->up();
    }
}
