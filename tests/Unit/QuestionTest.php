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
}
