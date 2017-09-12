<?php

namespace Tests\Feature;

use App\BankAccount;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Exceptions\ExistingBankAccountException;
use App\OfficeUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessTest extends TestCase
{
    use RefreshDatabase;

    public $business;

    public function setUp()
    {
        parent::setUp();
        $this->business = factory(Business::class)->create();
    }

    public function testBusinessCanBeCreated()
    {
        $this->assertTrue(true);
    }

    public function testBusinessCanHaveOfficeUsers()
    {
        $user1 = factory(OfficeUser::class)->create();
        $user2 = factory(OfficeUser::class)->create();
        $user3 = factory(Client::class)->create();

        $this->business->users()->attach($user1);
        $this->business->users()->attach($user2);
        $this->business->clients()->attach($user3);

        $this->assertCount(2, $this->business->users);
    }

    public function testBusinessCanHaveClients()
    {
        $user1 = factory(Client::class)->create();
        $user2 = factory(Client::class)->create();
        $user3 = factory(Caregiver::class)->create();


        $this->business->clients()->attach($user1);
        $this->business->clients()->attach($user2);
        $this->business->caregivers()->attach($user3);

        $this->assertCount(2, $this->business->clients);
    }

    public function testBusinessCanHaveCaregivers()
    {
        $user1 = factory(Caregiver::class)->create();
        $user2 = factory(Caregiver::class)->create();
        $user3 = factory(Client::class)->create();

        $this->business->caregivers()->attach($user1);
        $this->business->caregivers()->attach($user2);
        $this->business->clients()->attach($user3);

        $this->assertCount(2, $this->business->caregivers);
    }

    public function testBusinessCanHaveABankAccount()
    {
        $account = factory(BankAccount::class)->make();
        $this->business->setBankAccount($account);

        $definedAccount = $this->business->bankAccount;
        $this->assertEquals($account->account_number, $definedAccount->account_number);
    }

    public function testBusinessCanNotAddAnOwnedBusinessBankAccount()
    {
        $newBusiness = factory(Business::class)->create();
        $account = factory(BankAccount::class)->make();
        $newBusiness->setBankAccount($account);

        $this->expectException(ExistingBankAccountException::class);
        $this->business->setBankAccount($account);
    }

    public function testBusinessCanNotAddAnOwnedUserBankAccount()
    {
        $user = factory(Client::class)->create();
        $account = factory(BankAccount::class)->make();
        $user->bankAccounts()->save($account);

        $this->expectException(ExistingBankAccountException::class);
        $this->business->setBankAccount($account);
    }


}
