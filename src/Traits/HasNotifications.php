<?php

namespace Sbkl\LaravelFirebaseMessaging\Traits;

use Sbkl\LaravelFirebaseMessaging\Models\Notification;

trait HasNotifications
{
    /**
     * Get all of the model's notifications.
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'model');
    }
}
