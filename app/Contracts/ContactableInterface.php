<?php
namespace App\Contracts;

use App\Address;
use App\PhoneNumber;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface ContactableInterface
{
    function name(): string;
    function getAddress(): ?Address;
    function getPhoneNumber(): ?PhoneNumber;
    function getHic(): ?string;
    function getBirthdate(): ?string;
}