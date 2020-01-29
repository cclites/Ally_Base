<?php

namespace Tests\Feature;

use App\Business;
use Tests\CreatesBusinesses;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Question;

class ManageQuestionsTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses;

    public function setUp() : void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->createBusinessWithUsers();
    }

    /** @test */
    public function an_office_user_can_create_a_custom_question()
    {
        $this->actingAs($this->officeUser->user);
        
        $this->assertCount(0, $this->business->questions);

        $data = factory(Question::class)->make();

        $this->postJson(route('business.questions.store')."?business={$this->business->id}", $data->toArray())
            ->assertStatus(200);

        $this->assertCount(1, $this->business->fresh()->questions);
    }

    /** @test */
    public function a_custom_question_can_be_required()
    {
        $this->actingAs($this->officeUser->user);
        
        $data = factory(Question::class)->make(['required' => true]);

        $this->postJson(route('business.questions.store')."?business={$this->business->id}", $data->toArray())
            ->assertStatus(200);

        $this->assertEquals(1, $this->business->fresh()->questions()->first()->required);
    }

    /** @test */
    public function a_custom_question_does_not_require_a_client_type()
    {
        $this->actingAs($this->officeUser->user);
        
        $data = factory(Question::class)->make();

        unset($data['client_type']);

        $this->postJson(route('business.questions.store')."?business={$this->business->id}", $data->toArray())
            ->assertStatus(200);

        $this->assertNull($this->business->questions()->first()->client_type);
    }

    /** @test */
    public function a_custom_question_requires_a_question()
    {
        $this->withExceptionHandling();

        $this->actingAs($this->officeUser->user);

        $this->postJson(route('business.questions.store')."?business={$this->business->id}", [])
            ->assertJsonValidationErrors(['question']);

        $this->assertCount(0, Question::all());
    }
    
    /** @test */
    public function an_office_user_can_get_a_list_of_their_questions()
    {
        $this->actingAs($this->officeUser->user);
        
        factory(Question::class, 5)->create(['business_id' => $this->business->id]);
     
        $this->assertCount(5, Question::all());

        $this->getJson(route('business.questions.index')."?business={$this->business->id}")
            ->assertStatus(200)
            ->assertJsonCount(5);
    }
    
    /** @test */
    public function an_office_user_shouldnt_see_another_businesses_questions()
    {
        $this->actingAs($this->officeUser->user);

        $otherBusiness = factory(Business::class)->create();
        factory(Question::class, 5)->create(['business_id' => $otherBusiness->id]);

        $this->assertCount(5, Question::all());

        $this->getJson(route('business.questions.index')."?business={$this->business->id}")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }
    
    /** @test */
    public function an_office_user_can_delete_a_question()
    {
        $this->actingAs($this->officeUser->user);
        
        $question = factory(Question::class)->create(['business_id' => $this->business->id]);

        $this->assertCount(1, $this->business->questions);

        $this->delete(route('business.questions.destroy', ['question' => $question->id]))
            ->assertStatus(200);

        $this->assertCount(0, $this->business->fresh()->questions);
    }

    /** @test */
    public function an_office_user_cannot_delete_another_businesses_question()
    {
        $this->withExceptionHandling();

        $this->actingAs($this->officeUser->user);

        $otherBusiness = factory(Business::class)->create();
        $question = factory(Question::class)->create(['business_id' => $otherBusiness->id]);

        $this->assertCount(1, Question::all());

        $this->delete(route('business.questions.destroy', ['question' => $question->id]))
            ->assertStatus(403);

        $this->assertCount(1, Question::all());
    }

    /** @test */
    public function an_office_user_can_update_a_question()
    {
        $this->actingAs($this->officeUser->user);

        $question = factory(Question::class)->create(['business_id' => $this->business->id]);

        $data = [
            'question' => 'new question?',
            'client_type' => 'medicaid',
            'required' => '1',
        ];

        $this->patchJson(route('business.questions.update', ['question' => $question->id]), $data)
            ->assertStatus(200)
            ->assertJsonFragment($data);

        $question = $question->fresh();

        $this->assertEquals('new question?', $question->question);
        $this->assertEquals(1, $question->required);
        $this->assertEquals('medicaid', $question->client_type);
    }

    /** @test */
    public function an_office_user_cannot_update_anther_businesses_questions()
    {
        $this->withExceptionHandling();

        $this->actingAs($this->officeUser->user);

        $otherBusiness = factory(Business::class)->create();
        $question = factory(Question::class)->create(['business_id' => $otherBusiness->id]);

        $data = [
            'question' => 'new question?',
            'client_type' => 'medicaid',
            'required' => '1',
        ];

        $this->patchJson(route('business.questions.update', ['question' => $question->id]), $data)
            ->assertStatus(403);

        $this->assertEquals($question->question, $question->fresh()->question);
    }
}
