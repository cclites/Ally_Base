<?php
namespace App\Events\Contracts;

use App\Client;

interface ClientEvent
{
    public function __construct(Client $client);
    public function getClient(): Client;
}