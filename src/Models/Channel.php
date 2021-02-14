<?php

namespace Sbkl\LaravelFirebaseMessaging\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $guarded = [];

    protected $table = 'firebase_channels';

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'firebase_subscriptions');
    }
}
