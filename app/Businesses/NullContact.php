<?php
namespace App\Businesses;

use App\Address;
use App\Contracts\ContactableInterface;
use App\PhoneNumber;

class NullContact implements ContactableInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var \App\Address|null
     */
    private $address;
    /**
     * @var \App\PhoneNumber|null
     */
    private $phoneNumber;

    public function __construct($name = '', ?Address $address = null, ?PhoneNumber $phoneNumber = null)
    {
        $this->name = $name;
        $this->address = $address;
        $this->phoneNumber = $phoneNumber;
    }

    function name(): string
    {
        return '';
    }

    function getAddress(): ?Address
    {
        return null;
    }

    function getPhoneNumber(): ?PhoneNumber
    {
        return null;
    }

    function getHic(): ?string
    {
        return null;
    }

    function getBirthdate(): ?string
    {
        return null;
    }
}