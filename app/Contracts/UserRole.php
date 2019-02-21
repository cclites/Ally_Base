<?php
namespace App\Contracts;

interface UserRole extends ContactableInterface
{
    public function getRoleType();
    public function user();
    public function nameLastFirst();
    public function addresses();
    public function phoneNumbers();
    public function bankAccounts();
    public function creditCards();
    public function documents();
}