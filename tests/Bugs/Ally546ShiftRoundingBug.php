<?php

namespace Tests\Bugs;

use App\Businesses\Settings;
use App\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Ally546ShiftRoundingBug extends TestCase
{
    use RefreshDatabase;

    function test_individual_rounding_should_not_be_98()
    {
        $shift = factory(Shift::class)->create([
            'checked_in_time' => '2018-10-02 12:00:40',
            'checked_out_time' => '2018-10-02 18:01:12',
        ]);

        app()->bind(Settings::class, function() {
            $mock = \Mockery::mock(Settings::class);
            $mock->shouldReceive('get')->andReturn('individual');
            return $mock;
        });

        $duration = $shift->duration(true);
        $this->assertEquals(6, $duration);
    }

}
