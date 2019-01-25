<?php

namespace Tests\Feature;

use App\Billing\Payments\Methods\BankAccount;
use App\Caregiver;
use App\Client;
use App\Billing\Payments\Methods\CreditCard;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EncryptedDataTest extends TestCase
{
    use RefreshDatabase;

    public function testSsnEncryption()
    {
        $ssn       = '123-45-6789';
        $caregiver = factory(Caregiver::class)->create(['ssn' => $ssn]);
        $encrypted = $caregiver->getAttributes()['ssn'];
        $decrypted = $caregiver->ssn;
        $this->assertNotEquals($encrypted, $decrypted);
        $this->assertGreaterThan(127, strlen($encrypted));
        $this->assertEquals($decrypted, \Crypt::decrypt($encrypted));
        $this->assertEquals($decrypted, $ssn);
    }

    public function testCreditCardEncryption()
    {
        $ccNumber = '1234567812345678';
        $client = factory(Client::class)->create();
        $cc = factory(CreditCard::class)->make(['number' => $ccNumber]);
        $client->user->creditCards()->save($cc);

        $decrypted = $cc->number;
        $encrypted = $cc->getAttributes()['number'];

        $this->assertNotEquals($encrypted, $decrypted);
        $this->assertGreaterThan(127, strlen($encrypted));
        $this->assertEquals($decrypted, \Crypt::decrypt($encrypted));
        $this->assertEquals($decrypted, $ccNumber);
    }

    public function testBankAccountEncryption()
    {
        $accountNo = '123456789';
        $client = factory(Client::class)->create();
        $account = factory(BankAccount::class)->make(['account_number' => $accountNo]);
        $client->user->bankAccounts()->save($account);

        $decrypted = $account->account_number;
        $encrypted = $account->getAttributes()['account_number'];

        $this->assertNotEquals($encrypted, $decrypted);
        $this->assertGreaterThan(127, strlen($encrypted));
        $this->assertEquals($decrypted, \Crypt::decrypt($encrypted));
        $this->assertEquals($decrypted, $accountNo);
    }
}
