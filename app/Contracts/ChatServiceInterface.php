<?php
namespace App\Contracts;

interface ChatServiceInterface
{
    /**
     * @return bool
     */
    public function isAvailable();

    /**
     * @param $channel
     * @return void
     */
    public function setChannel($channel);

    /**
     * @param $channel
     * @return void
     */
    public function setUsername($username);

    /**
     * @param string $message
     * @return bool
     */
    public function post($message);
}