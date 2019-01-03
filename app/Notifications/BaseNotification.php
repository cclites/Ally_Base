<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\SystemChannel;
use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use App\PhoneNumber;
use App\Jobs\SendTextMessage;

class BaseNotification extends Notification
{
    use Queueable;
    
    /**
     * Switch to disable notification in the system.
     *
     * @var boolean
     */
    public static $disabled = false;

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
     * The action text.
     *
     * @var string
     */
    protected $action;

    /**
     * The action URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Get the notification's unique identifier.
     *
     * @return string
     */
    public static function getKey()
    {
        if (empty(static::$key)) {
            return snake_case(basename(str_replace('\\', '/', get_called_class())));
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

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->line($this->getMessage());

        if (! empty($this->action) && ! empty($this->url) ) {
            $message->action($this->action, $this->url);
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->getMessage(),
            'action' => $this->action,
            'url' => $this->url,
        ];
    }

    /**
     * Get the SMS representation of the notification from a business chain.
     *
     * @param  mixed  $notifiable
     * @return SendTextMessage
     * @throws \Exception
     */
    public function toSmsFromChain($notifiable, $businessChain)
    {
        $number = new PhoneNumber();
        $number->input($notifiable->notification_phone);

        // send from the first business on the chain that has an outgoing number set up
        $business = $businessChain->businesses()->whereNotNull('outgoing_sms_number')->first();
        if (empty($business)) {
            return null;
        }

        return new SendTextMessage(
            $number->number(false),
            $this->getMessage() . ' ' . $this->url,
            $business->outgoing_sms_number
        );
    }

    /**
     * Get the SMS representation of the notification from a business chain.
     *
     * @param  mixed  $notifiable
     * @return SendTextMessage
     * @throws \Exception
     */
    public function toSmsFromBusiness($notifiable, $business)
    {
        $number = new PhoneNumber();
        $number->input($notifiable->notification_phone);

        // send from the current business if an outgoing number set up
        if (empty($business->outgoing_sms_number)) {
            return null;
        }

        return new SendTextMessage(
            $number->number(false),
            $this->getMessage() . ' ' . $this->url,
            $business->outgoing_sms_number
        );
    }
}
