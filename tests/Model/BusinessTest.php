<?php

namespace Tests\Model;

use App\Billing\Payments\Methods\BankAccount;
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

    public function setUp() : void
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
        $user3 = factory(Caregiver::class)->create();

        $this->business->users()->attach($user1);
        $this->business->users()->attach($user2);
        $this->business->assignCaregiver($user3);

        $this->assertCount(2, $this->business->users);
    }

    public function testBusinessCanHaveClients()
    {
        $user1 = factory(Client::class)->create();
        $user2 = factory(Client::class)->create();
        $user3 = factory(Caregiver::class)->create();


        $this->business->clients()->save($user1);
        $this->business->clients()->save($user2);
        $this->business->assignCaregiver($user3);

        $this->assertCount(2, $this->business->clients);
    }

    public function testBusinessCanHaveCaregivers()
    {
        $user1 = factory(Caregiver::class)->create();
        $user2 = factory(Caregiver::class)->create();
        $user3 = factory(OfficeUser::class)->create();

        $this->business->assignCaregiver($user1);
        $this->business->assignCaregiver($user2);
        $this->business->users()->attach($user3);

        $this->assertCount(2, $this->business->caregivers);
    }

    public function testBusinessCanHaveABankAccount()
    {
        $account = factory(BankAccount::class)->make();
        $this->business->setBankAccount('bankAccount', $account);

        $definedAccount = $this->business->bankAccount;
        $this->assertEquals($account->account_number, $definedAccount->account_number);
    }

    public function testBusinessCanNotAddAnOwnedBusinessBankAccount()
    {
        $newBusiness = factory(Business::class)->create();
        $account = factory(BankAccount::class)->make();
        $newBusiness->setBankAccount('bankAccount', $account);

        $this->expectException(ExistingBankAccountException::class);
        $this->business->setBankAccount('bankAccount', $account);
    }

    public function testBusinessCanNotAddAnOwnedUserBankAccount()
    {
        $user = factory(Client::class)->create();
        $account = factory(BankAccount::class)->make();
        $user->bankAccounts()->save($account);

        $this->expectException(ExistingBankAccountException::class);
        $this->business->setBankAccount('bankAccount', $account);
    }

    /** @test */
    public function it_can_have_custom_questions()
    {
        factory(\App\Question::class, 3)->create(['business_id' => $this->business->id]);
        
        $this->assertCount(3, $this->business->fresh()->questions);

        $this->assertInstanceOf(\App\Question::class, $this->business->questions[0]);
    }
}
