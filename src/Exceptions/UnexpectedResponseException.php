<?php

namespace Sbkl\LaravelFirebaseMessaging\Exceptions;

use Exception;

class UnexpectedResponseException extends Exception
{
    /**
     * UnexpectedResponseException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $message = 'Unexpected response was received from Firebase API.',
        $code = 500,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
