<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Question;
use App\Business;
use App\ClientType;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_business()
    {
        $question = factory(Question::class)->create();

        $this->assertInstanceOf(Business::class, $question->business);
    }

    /** @test */
    public function it_has_an_optional_client_type()
    {
        $question = factory(Question::class)->create();

        $question->update(['client_type' => ClientType::LTCI]);

        $this->assertEquals(ClientType::LTCI, $question->fresh()->client_type);

        $question->update(['client_type' => null]);

        $this->assertNull($question->fresh()->client_type);
    }

    /** @test */
    public function they_can_be_filtered_by_client_type()
    {
        $question = factory(Question::class)->create(['client_type' => null]);

        $business = $question->business;

        factory(Question::class)->create(['client_type' => ClientType::MEDICAID, 'business_id' => $business->id]);
        factory(Question::class)->create(['client_type' => ClientType::LTCI, 'business_id' => $business->id]);
        factory(Question::class)->create(['client_type' => ClientType::PRIVATE_PAY, 'business_id' => $business->id]);
        
        $this->assertCount(4, $business->fresh()->questions);

        $this->assertCount(2, $business->fresh()->questions()->forType(ClientType::MEDICAID)->get());

        $this->assertCount(2, $business->fresh()->questions()->forType(ClientType::PRIVATE_PAY)->get());

        $this->assertCount(2, $business->fresh()->questions()->forType(ClientType::LTCI)->get());
    }
}
