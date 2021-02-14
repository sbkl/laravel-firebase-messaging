<?php

namespace Sbkl\LaravelFirebaseMessaging\Tests\Unit;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Sbkl\LaravelFirebaseMessaging\Facades\Firebase;
use Sbkl\LaravelFirebaseMessaging\Models\Channel;
use Sbkl\LaravelFirebaseMessaging\Models\Notification;
use Sbkl\LaravelFirebaseMessaging\Models\User;
use Sbkl\LaravelFirebaseMessaging\Tests\LaravelTestCase;

class UserTest extends LaravelTestCase
{
    use ArraySubsetAsserts;

    /** @test */
    public function a_user_can_subscribe_to_a_channel()
    {
        $user = User::factory()->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        $user->subscribe($channel, $this->token);

        $this->assertDatabaseHas('firebase_subscriptions', [
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);
    }

    /** @test */
    public function a_user_can_unsubscribe_to_a_channel()
    {
        $user = User::factory()->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        $user->subscribe($channel, $this->token);

        $this->assertDatabaseHas('firebase_subscriptions', [
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);

        $user->unsubscribe($channel, $this->token);

        $this->assertDatabaseMissing('firebase_subscriptions', [
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);
    }

    /** @test */
    public function a_user_can_get_their_subscriptions()
    {
        $user = User::factory()->create();

        $channel1 = Channel::create([
            'name' => 'Channel1',
        ]);

        $channel2 = Channel::create([
            'name' => 'Channel2',
        ]);

        $subscription1 = $user->subscribe($channel1, $this->token);

        $subscription2 = $user->subscribe($channel1, $this->token);

        $subscription3 = $user->subscribe($channel2, $this->token);

        $this->assertEquals(get_class($subscription1), get_class($user->firebaseSubscriptions[0]));

        $this->assertEquals(get_class($subscription2), get_class($user->firebaseSubscriptions[1]));

        $this->assertEquals(get_class($subscription3), get_class($user->firebaseSubscriptions[2]));

        $this->assertEquals($subscription1->toArray(), $user->firebaseSubscriptions[0]->toArray());

        $this->assertEquals($subscription2->toArray(), $user->firebaseSubscriptions[1]->toArray());

        $this->assertEquals($subscription3->toArray(), $user->firebaseSubscriptions[2]->toArray());
    }

    /** @test */
    public function a_user_can_get_their_notifications()
    {
        $user = User::factory()->create();

        $channel1 = Channel::create([
            'name' => 'Channel1',
        ]);

        $channel2 = Channel::create([
            'name' => 'Channel2',
        ]);

        $user->subscribe($channel1, $this->token);

        $user->subscribe($channel1, $this->token);

        $user->subscribe($channel2, $this->token);

        $notification1 = [
            'title' => 'User test notifications 1',
            'body' => 'This is coming from the package 1',
            'data' => [
                'someData' => 'goes here',
            ],
        ];

        Firebase::notify(['Channel1', 'Channel2'], $notification1);

        $notification2 = [
            'title' => 'User test notifications 2',
            'body' => 'This is coming from the package 2',
            'data' => [
                'someData' => 'goes here',
            ],
        ];

        Firebase::notify(['Channel1', 'Channel2'], $notification2);

        $notifications = Notification::all();

        $user_notifications = $user->firebaseNotifications;

        $this->assertEquals($notifications->count(), $user_notifications->count());

        $this->assertEquals(get_class($notifications[0]), get_class($user_notifications[0]));

        $this->assertEquals(get_class($notifications[1]), get_class($user_notifications[1]));

        $this->assertArraySubset($notifications[0]->toArray(), $user_notifications[0]->toArray());

        $this->assertArraySubset($notifications[1]->toArray(), $user_notifications[1]->toArray());
    }

    /** @test */
    public function it_can_read_a_notification()
    {
        $user = User::factory()->create();

        $user2 = User::factory()->create();

        $channel = Channel::create([
            'name' => 'Channel1',
        ]);

        $user->subscribe($channel, $this->token);

        $user2->subscribe($channel, $this->token);

        $notification1 = [
            'title' => 'User test notifications 1',
            'body' => 'This is coming from the package 1',
            'data' => [
                'someData' => 'goes here',
            ],
        ];

        $notification2 = [
            'title' => 'User test notifications 1',
            'body' => 'This is coming from the package 1',
            'data' => [
                'someData' => 'goes here',
            ],
        ];

        Firebase::notify(['Channel1'], $notification1);

        Firebase::notify(['Channel1'], $notification2);

        $databaseNotification = Notification::first();

        $this->assertNull($user->firebaseNotifications()->first()->pivot->read_at);

        $this->assertFalse($user->firebaseNotificationRead($databaseNotification->id));

        $this->assertTrue($user->firebaseNotificationUnread($databaseNotification->id));

        $this->assertFalse($databaseNotification->read($user->id));

        $this->assertFalse($databaseNotification->read($user->id));

        $this->assertTrue($databaseNotification->unread($user->id));

        $user->markNotificationAsRead($databaseNotification->id);

        $this->assertNotNull($user->firebaseNotifications()->first()->pivot->read_at);

        $this->assertTrue($user->firebaseNotificationRead($databaseNotification->id));

        $this->assertFalse($user->firebaseNotificationUnread($databaseNotification->id));

        $this->assertTrue($databaseNotification->read($user->id));

        $this->assertFalse($databaseNotification->unread($user->id));

        $user->markNotificationAsUnread($databaseNotification->id);

        $this->assertNull($user->firebaseNotifications()->first()->pivot->read_at);

        $databaseNotification->markAsRead($user->id);

        $this->assertNotNull($user->firebaseNotifications()->first()->pivot->read_at);

        $databaseNotification->markAsUnread($user->id);

        $this->assertNull($user->firebaseNotifications()->first()->pivot->read_at);
    }
}
