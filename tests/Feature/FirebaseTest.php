<?php

namespace Sbkl\LaravelFirebaseMessaging\Tests\Feature;

use Sbkl\LaravelFirebaseMessaging\Exceptions\FirebaseException;
use Sbkl\LaravelFirebaseMessaging\Facades\Firebase;
use Sbkl\LaravelFirebaseMessaging\Models\Channel;
use Sbkl\LaravelFirebaseMessaging\Models\Notification;
use Sbkl\LaravelFirebaseMessaging\Models\User;
use Sbkl\LaravelFirebaseMessaging\Tests\LaravelTestCase;

class FirebaseTest extends LaravelTestCase
{
    /** @test */
    public function it_can_create_a_channel()
    {
        $channelName = 'Test';

        Channel::create([
            'name' => $channelName,
        ]);

        $this->assertDatabaseHas('firebase_channels', [
            'name' => $channelName,
            'deactivated_at' => null,
        ]);
    }

    /** @test */
    public function it_can_subscribe_to_a_channel()
    {
        $user = User::factory()->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Firebase::subscribe($user, $channel, $this->token);

        $this->assertDatabaseHas('firebase_subscriptions', [
            'user_id' => $user->id,
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);
    }

    /** @test */
    public function it_can_unsubscribe_from_a_channel()
    {
        $user = User::factory()->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Firebase::subscribe($user, $channel, $this->token);

        $this->assertDatabaseHas('firebase_subscriptions', [
            'user_id' => $user->id,
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);

        Firebase::unsubscribe($channel, $this->token);

        $this->assertDatabaseMissing('firebase_subscriptions', [
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);
    }

    /** @test */
    public function a_notification_must_have_a_title_or_a_body()
    {
        $user = User::factory()->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Firebase::subscribe($user, $channel, $this->token);

        $notification = [];

        try {
            Firebase::notify(['Test'], $notification);
        } catch (FirebaseException $error) {
            $this->assertEquals('Sbkl\LaravelFirebaseMessaging\Exceptions\FirebaseException', get_class($error));
        }
    }

    /** @test */
    public function it_can_create_recipients()
    {
        $user1 = User::factory()->create();

        $user2 = User::factory()->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Firebase::subscribe($user1, $channel, $this->token);

        Firebase::subscribe($user2, $channel, $this->token);

        $notification = [
            'title' => 'Laravel Push Notifications',
            'body' => 'This is coming from the package',
            'data' => [
                'hello' => 'world'
            ],
        ];

        $response = Firebase::notify([$channelName], $notification);

        $this->assertDatabaseHas('firebase_notifications', [
            'type' => null,
            'title' => $notification['title'],
            'body' => $notification['body'],
            'data' => '{"hello":"world"}',
        ]);

        $databaseNotification = Notification::first();

        $this->assertDatabaseHas('firebase_notification_user', [
            'firebase_notification_id' => $databaseNotification->id,
            'user_id' => (string) $user1->id,
            'read_at' => null,
        ]);

        $this->assertDatabaseHas('firebase_notification_user', [
            'firebase_notification_id' => $databaseNotification->id,
            'user_id' => (string) $user1->id,
            'read_at' => null,
        ]);

        // $this->assertEquals('ok', $response[0]['status']);

        // $this->assertEquals('DeviceNotRegistered', $response[0]['details']['error']);
    }

    /** @test */
    public function it_can_send_a_notification()
    {
        $user = User::factory()->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Firebase::subscribe($user, $channel, $this->token);

        $notification = [
            'title' => 'Laravel Push Notifications',
            'body' => 'This is coming from the package',
        ];

        $response = Firebase::notify([$channelName], $notification);

        $this->assertDatabaseHas('firebase_notifications', [
            'type' => null,
            'title' => $notification['title'],
            'body' => $notification['body'],
        ]);
        // $this->assertEquals('ok', $response[0]['status']);
    }

    /** @test */
    public function it_can_send_multiple_notifications()
    {
        $user1 = User::factory()->create();

        $user2 = User::factory()->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Firebase::subscribe($user1, $channel, $this->token);

        // firebase::subscribe($user, $channel, $token);
        $user2->subscribe($channel, $this->token);

        $notification = [
            'title' => 'Laravel Push Notifications Multiple',
            'body' => 'This is coming from the package',
        ];

        $response = Firebase::notify([$channelName], $notification);

        // $this->assertEquals('ok', $response[0]['status']);

        // $this->assertEquals('error', $response[1]['status']);

        // $this->assertEquals(2, count($response));
    }
}
