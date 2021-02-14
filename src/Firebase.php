<?php

namespace Sbkl\LaravelFirebaseMessaging;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Sbkl\LaravelFirebaseMessaging\Models\Channel;
use Sbkl\LaravelFirebaseMessaging\Models\Notification;
use Sbkl\LaravelFirebaseMessaging\Exceptions\FirebaseException;
use Sbkl\LaravelFirebaseMessaging\Exceptions\UnexpectedResponseException;

class Firebase
{
    /**
     * The registrar instance that manages the tokens.
     *
     * @var FirebaseRegistrar
     */
    private $registrar;
    protected $title;
    protected $body;
    protected $badge = 0;
    protected $sound = 'default';
    protected $data = null;
    protected $config;

    /**
     * Firebase constructor.
     *
     * @param FirebaseRegistrar $firebaseRegistrar
     */
    public function __construct(FirebaseRegistrar $firebaseRegistrar)
    {
        $this->registrar = $firebaseRegistrar;
    }

    /**
     * Creates an instance of this class with the normal setup
     * It uses the firebaseFileDriver as the repository.
     *
     * @return Firebase
     */
    public static function databaseSetup()
    {
        return new self(new FirebaseRegistrar(new FirebaseDatabaseDriver()));
    }

    /**
     * Subscribes a given interest to the Firebase Push Notifications.
     *
     * @param $subscriber
     * @param $interest
     * @param $token
     *
     * @return Sbkl\LaravelFirebaseMessaging\Models\Subscription
     */
    public function subscribe($subscriber, $channel, $token = null)
    {
        $subscription = $this->registrar->registerInterest($subscriber, $channel, $token);

        return $subscription;
    }

    /**
     * Unsubscribes a given interest from the Firebase Push Notifications.
     *
     * @param $interest
     * @param $token
     *
     * @return bool
     */
    public function unsubscribe($interest, $token = null)
    {
        return $this->registrar->removeInterest($interest, $token);
    }

    /**
     * Send a notification via the Firebase Push Notifications Api.
     *
     * @param array $interests
     * @param array $data
     * @param bool $debug
     *
     * @throws FirebaseException
     * @throws UnexpectedResponseException
     *
     * @return array|bool
     */
    public function notify(array $channelNames, array $notification, $debug = false)
    {
        $this->validateNotifications($channelNames, $notification);

        $this->setData($notification);

        $this->apnsConfig();
        // Create the notification
        $databaseNotification = Notification::create(array_merge(
            ['id' => Str::uuid()->toString()],
            isset($notification['model']) ? ['model_type' => get_class($notification['model'])] : [],
            isset($notification['model']) ? ['model_id' => $notification['model']->id] : [],
            isset($notification['title']) ? ['title' => $notification['title']] : [],
            isset($notification['body']) ? ['body' => $notification['body']] : [],
            isset($notification['data']) ? ['data' => $notification['data']] : [],
        ));

        $response = [];

        Channel::whereIn('name', $channelNames)->whereHas('subscriptions')->chunk(1, function ($channels) use ($databaseNotification, $notification, $debug, &$response) {
            // $postData = [];

            // Gets the Firebase tokens and recipients
            [$tokens, $recipientIds] = $this->registrar->getInterests($channels);

            $existingRecipients = $databaseNotification->fresh()->recipients()->whereIn('user_id', $recipientIds)->pluck('user_id');

            $databaseNotification->fresh()->recipients()->attach(collect($recipientIds)->diff($existingRecipients));

            if (! empty($tokens)) {
                // foreach ($tokens as $token) {
                //     $postData[] = $notification + ['to' => $token];
                // }
                // dd($this->registrar->messaging);
                $message = CloudMessage::new();
                $message = $message->withApnsConfig($this->config);
                if($this->data) {
                    $message = $message->withData($this->data);
                }
                
                $this->registrar->messaging->sendMulticast($message, $tokens);                
                // $ch = $this->prepareCurl();

                // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

                // $response = $this->executeCurl($ch);

                // If the notification failed completely, throw an exception with the details
                // if ($debug && $this->failedCompletely($response, $tokens)) {
                //     throw FirebaseException::failedCompletelyException($response);
                // }
            }
        });

        return $response;
    }

    private function validateNotifications(array $channelNames, array $notification)
    {
        if (count($channelNames) == 0) {
            throw new FirebaseException('Channels array must not be empty.');
        }

        if (! isset($notification['title']) && ! isset($notification['body'])) {
            throw FirebaseException::emptyNotification();
        }

        if (isset($notification['model']) && ! $notification['model'] instanceof Model) {
            throw FirebaseException::wrongModelInstance();
        }
    }

    private function setData(array $notification): void
    {
        if(isset($notification['title'])) {
            $this->title = $notification['title'];
        }
        if(isset($notification['body'])) {
            $this->body = $notification['body'];
        }
        if(isset($notification['badge'])) {
            $this->badge = $notification['badge'];
        }
        if(isset($notification['sound'])) {
            $this->sound = $notification['sound'];
        }
        if(isset($notification['data'])) {
            $this->data = $notification['data'];
        }
    }

    private function apnsConfig()
    {
        $this->config = ApnsConfig::fromArray([
            'headers' => [
                'apns-priority' => '10',
            ],
            'payload' => [
                'aps' => [
                    'alert' => [
                        'title' => $this->title,
                        'body' => $this->body,
                    ],
                    'badge' => $this->badge,
                    'sound' => $this->sound,
                ],
            ],
        ]);
    }
    // /**
    //  * Determines if the request we sent has failed completely.
    //  *
    //  * @param array $response
    //  * @param array $recipients
    //  *
    //  * @return bool
    //  */
    // private function failedCompletely(array $response, array $recipients)
    // {
    //     $numberOfRecipients = count($recipients);
    //     $numberOfFailures = 0;

    //     foreach ($response as $item) {
    //         if ($item['status'] === 'error') {
    //             $numberOfFailures++;
    //         }
    //     }

    //     return $numberOfFailures === $numberOfRecipients;
    // }
}
