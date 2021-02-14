<?php

namespace Sbkl\LaravelFirebaseMessaging\Exceptions;

class FirebaseException extends \Exception
{
    /**
     * Formats the exception for a completely failed request.
     *
     * @param $response
     *
     * @return static
     */
    public static function failedCompletelyException($response)
    {
        $message = '';
        foreach ($response as $key => $item) {
            if ($item['status'] === 'error') {
                $message .= $key == 0 ? '' : "\r\n";
                $message .= $item['message'];
            }
        }

        return new static($message);
    }

    public static function emptyNotification()
    {
        return new static('A notification must have a title or a body attribute.');
    }

    public static function wrongModelInstance()
    {
        return new static('model attribute must be an instance of Illuminate\Database\Eloquent\Model');
    }
}
