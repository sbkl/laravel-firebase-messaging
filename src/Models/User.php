<?php

namespace Sbkl\LaravelFirebaseMessaging\Models;

use Sbkl\LaravelFirebaseMessaging\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sbkl\LaravelFirebaseMessaging\Traits\Firebaseable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Firebaseable;

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
