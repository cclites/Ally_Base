<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Question;

class ManageQuestionsTest extends TestCase
{
    use RefreshDatabase;

    public $officeUser;
    public $client;
    public $caregiver;
    public $business;
    
    public function setUp()
    {
        parent::setUp();

        $this->client = factory('App\Client')->create();
        $this->business = $this->client->business;
        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->caregivers()->save($this->caregiver);
        
        $this->officeUser = factory('App\OfficeUser')->create();
        $this->officeUser->businesses()->attach($this->business->id);
    }

    /** @test */
    public function an_office_user_can_create_a_custom_question()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->officeUser->user);
        
        $this->assertCount(0, $this->business->questions);

        $data = factory(Question::class)->make();

        $this->postJson(route('business.questions.store'), $data->toArray())
            ->assertStatus(200);

        $this->assertCount(1, $this->business->fresh()->questions);
    }

    /** @test */
    public function a_custom_question_can_be_required()
    {
        $this->actingAs($this->officeUser->user);
        
        $data = factory(Question::class)->make(['required' => true]);

        $this->postJson(route('business.questions.store'), $data->toArray())
            ->assertStatus(200);

        $this->assertEquals(1, $this->business->fresh()->questions()->first()->required);
    }

    /** @test */
    public function a_custom_question_does_not_require_a_client_type()
    {
        $this->actingAs($this->officeUser->user);
        
        $data = factory(Question::class)->make();

        unset($data['client_type']);

        $this->postJson(route('business.questions.store'), $data->toArray())
            ->assertStatus(200);

        $this->assertNull($this->business->questions()->first()->client_type);
    }

    /** @test */
    public function a_custom_question_requires_a_question()
    {
        $this->actingAs($this->officeUser->user);
        
        $this->postJson(route('business.questions.store'), [])
            ->assertJsonValidationErrors(['question']);

        $this->assertCount(0, Question::all());
    }
    
    /** @test */
    public function an_office_user_can_get_a_list_of_their_questions()
    {
        $this->actingAs($this->officeUser->user);
        
        factory(Question::class, 5)->create(['business_id' => $this->business->id]);
     
        $this->assertCount(5, Question::all());

        $this->getJson(route('business.questions.index'))
            ->assertStatus(200)
            ->assertJsonCount(5);
    }
    
    /** @test */
    public function an_office_user_shouldnt_see_another_businesses_questions()
    {
        $this->actingAs($this->officeUser->user);
        
        factory(Question::class, 5)->create();

        $this->assertCount(5, Question::all());

        $this->getJson(route('business.questions.index'))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }
}
