<?php

namespace Tests\Controller\Client;

use App\Client;
use App\CreditCard;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @return void
     */
    public function testAClientCanAddACreditCard()
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
}
