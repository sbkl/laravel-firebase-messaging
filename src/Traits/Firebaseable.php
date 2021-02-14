<?php

namespace Sbkl\LaravelFirebaseMessaging\Traits;

use Sbkl\LaravelFirebaseMessaging\Facades\Firebase;
use Sbkl\LaravelFirebaseMessaging\Models\Notification;
use Sbkl\LaravelFirebaseMessaging\Models\Subscription;

trait Firebaseable
{
    /**
     * Get the notification entities that the users has.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function firebaseSubscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the notification entities that the users has.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function firebaseNotifications()
    {
        return $this->belongsToMany(Notification::class, 'firebase_notification_user', 'user_id', 'firebase_notification_id')->withPivot('read_at')->withTimestamps();
    }

    public function subscribe($channel, $token = null)
    {
        return Firebase::subscribe($this, $channel, $token);
    }

    public function unsubscribe($channel, $token)
    {
        return Firebase::unsubscribe($channel, $token);
    }

    public function markNotificationAsRead($notificationId)
    {
        $this->firebaseNotifications()->updateExistingPivot($notificationId, [
            'read_at' => now(),
        ]);
    }

    public function markNotificationAsUnread($notificationId)
    {
        $this->firebaseNotifications()->updateExistingPivot($notificationId, [
            'read_at' => null,
        ]);
    }

    public function firebaseNotificationRead($notificationId)
    {
        return $this->firebaseNotifications()->where('id', $notificationId)->first()->pivot->read_at !== null;
    }

    public function firebaseNotificationUnread($notificationId)
    {
        return $this->firebaseNotifications()->where('id', $notificationId)->first()->pivot->read_at === null;
    }
}
