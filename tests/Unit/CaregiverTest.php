<?php

namespace Tests\Unit;

use App\BankAccount;
use App\Business;
use App\Caregiver;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CaregiverTest extends TestCase
{
    use RefreshDatabase;

    public $caregiver;

    public function setUp()
    {
        parent::setUp();
        $this->caregiver = factory(Caregiver::class)->create();
    }

    public function testCaregiverCanBeCreated()
    {
        $this->assertTrue(true);
    }

    public function testCaregiverCanHaveADefaultBankAccount()
    {
        $account = factory(BankAccount::class)->make();
        $this->caregiver->setBankAccount($account);

        $definedAccount = $this->caregiver->bankAccount;
        $this->assertEquals($account->account_number, $definedAccount->account_number);
    }

    public function testCaregiverCanChangeDefaultBankAccount()
    {
        $account = factory(BankAccount::class)->make();
        $this->caregiver->setBankAccount($account);

        $account2 = factory(BankAccount::class)->make();
        $this->caregiver->bankAccounts()->save($account2);

        $this->caregiver->setBankAccount($account2);

        $this->assertEquals($this->caregiver->bankAccount->id, $account2->id);
    }

    public function testCaregiverCanWorkForMultipleBusinesses()
    {
        $business1 = factory(Business::class)->create();
        $business2 = factory(Business::class)->create();

        $this->caregiver->businesses()->attach($business1);
        $this->caregiver->businesses()->attach($business2);

        $this->assertCount(2, $this->caregiver->businesses);
    }


}
