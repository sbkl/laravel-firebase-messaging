<?php

namespace Sbkl\LaravelFirebaseMessaging\Tests\Unit;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Sbkl\LaravelFirebaseMessaging\Models\Channel;
use Sbkl\LaravelFirebaseMessaging\Models\User;
use Sbkl\LaravelFirebaseMessaging\Tests\LaravelTestCase;

class ChannelTest extends LaravelTestCase
{
    use ArraySubsetAsserts;

    /** @test */
    public function a_user_can_get_their_subscriptions()
    {
        $user1 = User::factory()->create();

        $user2 = User::factory()->create();

        $user3 = User::factory()->create();

        $channel = Channel::create([
            'name' => 'Channel1',
        ]);

        $user1->subscribe($channel, $this->token);

        $user2->subscribe($channel, $this->token);

        $user3->subscribe($channel, $this->token);

        $this->assertArraySubset($user1->toArray(), $channel->subscribers[0]->toArray());

        $this->assertArraySubset($user2->toArray(), $channel->subscribers[1]->toArray());

        $this->assertArraySubset($user3->toArray(), $channel->subscribers[2]->toArray());
    }
}
