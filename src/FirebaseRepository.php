<?php

namespace Sbkl\LaravelFirebaseMessaging;

use Sbkl\LaravelFirebaseMessaging\Models\Channel;

interface FirebaseRepository
{
    /**
     * Stores an Firebase token with a given identifier.
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function store($user, $channel, $token);

    /**
     * Retrieve an Firebase token with a given identifier.
     *
     * @param string $key
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function retrieve(Channel $channel);

    /**
     * Removes an Firebase token with a given identifier.
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function forget($channel, $token = null): bool;
}
