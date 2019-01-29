<?php

namespace Tests\Unit\Model;

use Illuminate\Database\QueryException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\StatusAlias;
use App\Business;

class StatusAliasTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_name()
    {
        $status = factory(StatusAlias::class)->create(['name' => 'Test']);
        $this->assertEquals('Test', $status->name);
    }

    /** @test */
    public function its_name_must_be_unique()
    {
        $status = factory(StatusAlias::class)->create(['name' => 'Test']);
        $this->assertCount(1, StatusAlias::all());

        $this->expectException(QueryException::class);
        $status2 = factory(StatusAlias::class)->create(['name' => 'Test']);
        $this->assertCount(1, StatusAlias::all());
    }

    /** @test */
    public function it_can_be_classified_as_an_active_status()
    {
        $status = factory(StatusAlias::class)->create(['active' => true]);
        $this->assertEquals(true, $status->active);
    }

    /** @test */
    public function it_can_be_classified_as_an_inactive_status()
    {
        $status = factory(StatusAlias::class)->create(['active' => false]);
        $this->assertEquals(false, $status->active);
    }

    /** @test */
    public function it_belongs_to_a_business()
    {
        $status = factory(StatusAlias::class)->create();
        $this->assertInstanceOf(Business::class, $status->business);
    }

    /** @test */
    public function it_has_a_user_type()
    {
        $status = factory(StatusAlias::class)->create(['type' => 'caregiver']);
        $this->assertEquals('caregiver', $status->type);
    }
}
