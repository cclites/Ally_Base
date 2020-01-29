<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\SystemChannel;
use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use App\PhoneNumber;
use App\Jobs\SendTextMessage;
use App\SystemNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Switch to disable notification in the system.
     *
     * @var boolean
     */
    const DISABLED = false;

    /**
     * The unique key to identify the notification type.
     *
     * @var string
     */
    const KEY = '';

    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = '';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = '';

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
     * Unique event id attribute
     *
     * @var array
     */
    protected $appends = ['event_id'];

    /**
     * Unique event id
     *
     * @var string
     */
    protected $event_id = null;

    /**
     * Used to set a unique event id
     *
     * BaseNotification constructor.
     */
    public function __construct()
    {
        $this->setEventId();
    }

    /**
     * Get the notification's unique identifier.
     *
     * @return string
     */
    public static function getKey()
    {
        if (empty(static::KEY)) {
            return Str::snake(basename(str_replace('\\', '/', get_called_class())));
        }

        return static::KEY;
    }

    /**
     * Get the notification's title.
     *
     * @return string
     */
    public static function getTitle()
    {
        return static::TITLE;
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        return static::MESSAGE;
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

    /**
     * Get the SystemNotification representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SystemNotification
     */
    public function toSystem($notifiable, $data = [])
    {
        return SystemNotification::make(array_merge([
            'user_id' => $notifiable->id,
            'key' => static::getKey(),
            'message' => static::getMessage(),
            'action_url' => $this->url,
            'action' => $this->action,
            'event_id' => $this->event_id,
        ], $data));
    }

    /**
     * Generate a unique event ID.
     *
     * @return void
     */
    public function setEventId() : void
    {
        $this->event_id = SystemNotification::generateUniqueEventId();
    }
}
