<?php
namespace App\Contracts;

interface UserRole extends BelongsToBusinessesInterface
{
    public function getRoleType();
    public function user();
    public function name();
    public function nameLastFirst();
    public function addresses();
    public function bankAccounts();
    public function creditCards();
    public function phoneNumbers();
    public function documents();
}