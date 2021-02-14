<?php

namespace Sbkl\LaravelFirebaseMessaging;

use Kreait\Firebase\Factory;
use Illuminate\Database\Eloquent\Collection;
use Sbkl\LaravelFirebaseMessaging\Exceptions\FirebaseRegistrarException;

class FirebaseRegistrar
{
    /**
     * Repository that manages the storage and retrieval.
     *
     * @var FirebaseRepository
     */
    private $repository;
    public $messaging;

    /**
     * FirebaseRegistrar constructor.
     *
     * @param FirebaseRepository $repository
     */
    public function __construct(FirebaseRepository $repository)
    {
        $this->repository = $repository;
        if(env('FIREBASE_CREDENTIALS')) {
            $this->messaging = app('firebase.messaging');
        } else {
            $factory = (new Factory)->withServiceAccount('firebase-admin.json');
            $this->messaging = $factory->createMessaging();
        }
    }

    /**
     * Registers the given token for the given interest.
     *
     * @param $interest
     * @param $token
     *
     * @throws FirebaseRegistrarException
     *
     * @return Sbkl\LaravelFirebaseMessaging\Models\Subscription
     */
    public function registerInterest($user, $channel, $token)
    {
        if ($token && ! $this->isValidFirebasePushToken($token)) {
            throw FirebaseRegistrarException::invalidToken();
        }

        $subscription = $this->repository->store($user, $channel, $token);

        if (! $subscription) {
            throw FirebaseRegistrarException::couldNotRegisterInterest();
        }

        return $subscription;
    }

    /**
     * Removes token of a given interest.
     *
     * @param $interest
     * @param $token
     *
     * @throws FirebaseRegistrarException
     *
     * @return bool
     */
    public function removeInterest($interest, $token = null)
    {
        if (! $this->repository->forget($interest, $token)) {
            throw FirebaseRegistrarException::couldNotRemoveInterest();
        }

        return true;
    }

    /**
     * Gets the tokens of the interests.
     *
     * @param array $interests
     *
     * @throws FirebaseRegistrarException
     *
     * @return array
     */
    public function getInterests(Collection $channels): array
    {
        $tokens = [];

        $recipientIds = [];

        $channels->each(function ($channel) use (&$tokens, &$recipientIds) {
            $subscriptions = $this->repository->retrieve($channel);

            $subscriptions->each(function ($subscription) use (&$tokens, &$recipientIds) {
                $recipientIds[] = $subscription->user_id;

                if (is_string($subscription->token)) {
                    $tokens[] = $subscription->token;
                }
            });
        });

        if (empty($tokens) && empty($recipientIds)) {
            throw FirebaseRegistrarException::emptyInterests();
        }

        return [collect($tokens)->unique()->toArray(), collect($recipientIds)->unique()->toArray()];
    }

    /**
     * Determines if a token is a valid firebase push token.
     *
     * @param string $token
     *
     * @return bool
     */
    private function isValidFirebasePushToken(string $token)
    {
        $result = $this->messaging->validateRegistrationTokens([$token]);
        return count($result['valid']) + count($result['unknown']) > 0;
    }
}
