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
        $caregiver = factory(Caregiver::class)->create();
        $confirmation = new Confirmation($caregiver);
        $confirmation->touchTimestamp();
        $url = route('confirm.caregiver', [$confirmation->getToken()]);

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertSee($caregiver->user->firstname);

    }
}
