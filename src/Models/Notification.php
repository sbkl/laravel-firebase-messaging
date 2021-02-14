<?php

namespace Sbkl\LaravelFirebaseMessaging\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'firebase_notifications';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the recipient entities that the notification has.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function recipients()
    {
        return $this->belongsToMany(User::class, 'firebase_notification_user', 'firebase_notification_id', 'user_id')->withPivot('read_at')->withTimestamps();
    }

    /**
     * Get the model entity related to the notification.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead($recipientId)
    {
        $this->recipients()->updateExistingPivot($recipientId, [
            'read_at' => now(),
        ]);
    }

    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread($recipientId)
    {
        $this->recipients()->updateExistingPivot($recipientId, [
            'read_at' => null,
        ]);
    }

    /**
     * Determine if a notification has been read.
     *
     * @return bool
     */
    public function read($recipientId)
    {
        return $this->recipients()->where('id', $recipientId)->first()->pivot->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     *
     * @return bool
     */
    public function unread($recipientId)
    {
        return $this->recipients()->where('id', $recipientId)->first()->pivot->read_at === null;
    }
}
