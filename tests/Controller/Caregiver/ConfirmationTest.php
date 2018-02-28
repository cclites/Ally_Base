<?php

namespace Tests\Controller\Caregiver;

use App\Caregiver;
use App\Confirmations\Confirmation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function testCaregiverCanCompleteConfirmation()
    {
        // todo complete confirmation test
        $business = factory(Business:class)
        $caregiver = factory(Caregiver::class)->create();
        $confirmation = new Confirmation($caregiver);
        $confirmation->touchTimestamp();
        $url = route('confirm.caregiver', [$confirmation->getToken()]);

        dump($url);
        $response = $this->get($url);

        dump($response->status());

        //$response->assertSeeText($caregiver->user->firstname);

    }
}