<?php
namespace App\Contracts;


interface CanBeConfirmedInterface
{
    public function sendConfirmationEmail();
    public function getEncryptedKey();
    public function getRoleType();
}