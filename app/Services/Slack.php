<?php
namespace App\Services;

use App\Contracts\ChatServiceInterface;

class Slack implements ChatServiceInterface
{
    protected $endpoint_url = '';
    public $channel = '#_helpdesk-portal';
    public $username = 'JTR Bot';
    public $icon_url = "";
    public $icon_emoji;

    public function __construct($endpoint_url)
    {
        $this->endpoint_url = $endpoint_url;
    }

    /**
     * @param $message
     * @return mixed
     */
    public function post($message)
    {
        $data = [
            'channel' => $this->channel,
            'username' => $this->username,
            'text' => $message,
        ];

        if ($this->icon_url) {
            $data['icon_url'] = $this->icon_url;
        }
        elseif ($this->icon_emoji) {
            $data['icon_emoji'] = $this->icon_emoji;
        }

        $payload = "payload=" . json_encode($data);

        $ch = curl_init($this->endpoint_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return ($this->channel && $this->endpoint_url);
    }

    /**
     * @param $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @param $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setIconUrl($url)
    {
        $this->icon_url = $url;
        return $this;
    }
}
