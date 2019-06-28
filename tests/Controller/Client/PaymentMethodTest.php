<?php

namespace Tests\Controller\Client;

use App\Client;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Payments\Methods\BankAccount;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp()
    {
        parent::setUp();
        $this->disableExceptionHandling();
    }

    /**
     * @return void
     */
    public function testAClientCanAddAPrimaryCreditCard()
    {
        $client = factory(Client::class)->create();

        $this->actingAs($client->user);
        $credit_card = factory(CreditCard::class)->make(['user_id' => $client->user->id]);

        $data = collect($credit_card->toArray())
            ->except('expiration_date', 'last_four', 'type', 'user_id')
            ->toArray();
        $data['number'] = $this->faker->creditCardNumber;
        $data['cvv'] = 123;

        $response = $this->post('/profile/payment/primary', $data);
        $response->assertStatus(200);

        $cards = CreditCard::where('user_id', $client->user->id)
            ->where('nickname', $credit_card->nickname)
            ->where('name_on_card', $credit_card->name_on_card)
            ->get();
        $this->assertCount(1, $cards);
    }

    public function testAClientCanAddAPrimaryBankAccount()
    {
        $client = factory(Client::class)->create();

        $this->actingAs($client->user);
        $bank_account = factory(BankAccount::class)->make([
            'user_id' => $client->user->id,
            'business_id' => null
        ]);
        $data = collect($bank_account->toArray())->except('last_four', 'user_id', 'business_id')->toArray();
        $data['routing_number'] = $this->faker->randomNumber(9, true);
        $data['routing_number_confirmation'] = $data['routing_number'];
        $data['account_number'] = $this->faker->bankAccountNumber;
        $data['account_number_confirmation'] = $data['account_number'];

        $response = $this->post('/profile/payment/primary', $data);

        $response->assertStatus(200);

        $accounts = BankAccount::where('user_id', $client->user->id)
            ->where('nickname', $bank_account->nickname)
            ->where('name_on_account', $bank_account->name_on_account)
            ->get();

        $this->assertCount(1, $accounts);
    }
}
