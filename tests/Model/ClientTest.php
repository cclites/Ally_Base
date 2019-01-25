<?php

namespace Tests\Model;

use App\Address;
use App\Billing\Payments\Methods\BankAccount;
use App\Business;
use App\Client;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Payment;
use App\PhoneNumber;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = factory(Client::class)->create();
    }

    public function testClientCanBeCreated()
    {
        $this->assertTrue(true);
    }

    public function testClientCanHaveCreditCards()
    {
        $cc1 = factory(CreditCard::class)->make();
        $cc2 = factory(CreditCard::class)->make();

        $this->client->creditCards()->save($cc1);
        $this->client->creditCards()->save($cc2);

        $testQuery = $this->client->creditCards()->where('id', $cc1->id);

        $this->assertCount(2, $this->client->creditCards);
        $this->assertEquals(1, $testQuery->count());
    }

    public function testClientCanNotSeeOtherCards()
    {
        $cc1 = factory(CreditCard::class)->make();
        $cc2 = factory(CreditCard::class)->make();
        $newClient = factory(Client::class)->create();
        $newClient->creditCards()->save($cc1);
        $newClient->creditCards()->save($cc2);

        $this->assertCount(0, $this->client->creditCards);
    }

    public function testClientCanHaveBankAccounts()
    {
        $account1 = factory(BankAccount::class)->make();
        $account2 = factory(BankAccount::class)->make();

        $this->client->bankAccounts()->save($account1);
        $this->client->bankAccounts()->save($account2);

        $testQuery = $this->client->bankAccounts()->where('id', $account1->id);

        $this->assertCount(2, $this->client->bankAccounts);
        $this->assertEquals(1, $testQuery->count());
    }

    public function testClientCanNotSeeOtherAccounts()
    {
        $account1 = factory(BankAccount::class)->make();
        $account2 = factory(BankAccount::class)->make();
        $newClient = factory(Client::class)->create();
        $newClient->bankAccounts()->save($account1);
        $newClient->bankAccounts()->save($account2);

        $this->assertCount(0, $this->client->bankAccounts);
    }

    public function testClientCanHavePhoneNumbers()
    {
        $phone1 = factory(PhoneNumber::class)->make();
        $phone2 = factory(PhoneNumber::class)->make();

        $this->client->phoneNumbers()->save($phone1);
        $this->client->phoneNumbers()->save($phone2);

        $testQuery = $this->client->phoneNumbers()->where('id', $phone1->id);

        $this->assertCount(2, $this->client->phoneNumbers);
        $this->assertEquals(1, $testQuery->count());
    }

    public function testClientCanNotSeeOtherNumbers()
    {
        $phone1 = factory(PhoneNumber::class)->make();
        $phone2 = factory(PhoneNumber::class)->make();
        $newClient = factory(Client::class)->create();
        $newClient->phoneNumbers()->save($phone1);
        $newClient->phoneNumbers()->save($phone2);

        $this->assertCount(0, $this->client->phoneNumbers);
    }

    public function testClientCanHaveAddresses()
    {
        $address1 = factory(Address::class)->make();
        $address2 = factory(Address::class)->make();

        $this->client->addresses()->save($address1);
        $this->client->addresses()->save($address2);

        $testQuery = $this->client->addresses()->where('id', $address1->id);

        $this->assertCount(2, $this->client->addresses);
        $this->assertEquals(1, $testQuery->count());
    }

    public function testClientCanNotSeeOtherAddresses()
    {
        $address1 = factory(Address::class)->make();
        $address2 = factory(Address::class)->make();
        $newClient = factory(Client::class)->create();
        $newClient->addresses()->save($address1);
        $newClient->addresses()->save($address2);

        $this->assertCount(0, $this->client->addresses);
    }

    public function testClientCanHavePayments()
    {
        $business = factory(Business::class)->create();
        $payment1 = factory(Payment::class)->make(['business_id' => $business->id]);
        $payment2 = factory(Payment::class)->make(['business_id' => $business->id]);

        $this->client->payments()->save($payment1);
        $this->client->payments()->save($payment2);

        $testQuery = $this->client->payments()->where('id', $payment1->id);

        $this->assertCount(2, $this->client->payments);
        $this->assertEquals(1, $testQuery->count());
    }

    public function testClientCanNotSeeOtherPayments()
    {
        $business = factory(Business::class)->create();
        $payment1 = factory(Payment::class)->make(['business_id' => $business->id]);
        $payment2 = factory(Payment::class)->make(['business_id' => $business->id]);
        $newClient = factory(Client::class)->create();
        $newClient->payments()->save($payment1);
        $newClient->payments()->save($payment2);

        $this->assertCount(0, $this->client->payments);
    }

}
