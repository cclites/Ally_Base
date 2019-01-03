<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Channels\SystemChannel;
use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;

class BaseNotification extends Notification
{
    use Queueable;
    
    /**
     * The unique key to identify the notification type.
     *
     * @var string
     */
    protected static $key;

    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    protected static $title;

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    protected static $message;

    /**
     * Get the notification's unique identifier.
     *
     * @return string
     */
    public static function getKey()
    {
        if (empty(static::$key)) {
            return static::class;
        }

        return static::$key;
    }

    /**
     * Get the notification's title.
     *
     * @return string
     */
    public static function getTitle()
    {
        return static::$title;
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        return static::$message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = [];

        if ($notifiable->shouldNotify(static::getKey(), 'system')) {
            array_push($via, SystemChannel::class);
        }

        if ($notifiable->shouldNotify(static::getKey(), 'mail')) {
            array_push($via, 'mail');
        }

        if ($notifiable->shouldNotify(static::getKey(), 'sms')) {
            array_push($via, SmsChannel::class);
        }

        return $via;
    }
}
