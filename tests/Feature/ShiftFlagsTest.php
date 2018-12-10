<?php

namespace Tests\Feature;

use App\Caregiver;
use App\Client;
use App\Shift;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShiftFlagsTest extends TestCase
{
    use RefreshDatabase;

    protected $caregiver;
    protected $client;

    protected function setUp()
    {
        parent::setUp();

        $this->client = factory(Client::class)->create();
        $this->caregiver = factory(Caregiver::class)->create();
    }

    /**
     * @test
     */
    public function shifts_at_different_times_should_not_have_a_duplicate_flag()
    {
        $shift1 = $this->createDuplicateShift('12:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('02:00:00', '08:00:00');

        $this->assertFalse($shift1->fresh()->hasFlag('duplicate'), 'The original shift should not have a duplicate flag');
        $this->assertFalse($shift2->hasFlag('duplicate'), 'The new shift should not have a duplicate flag');
    }

    /**
     * @test
     */
    public function a_shift_is_duplicated_by_an_exact_matching_clock()
    {
        $shift1 = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('10:00:00', '18:00:00');

        $this->assertTrue($shift1->fresh()->hasFlag('duplicate'), 'The original shift did not get the duplicate flag');
        $this->assertTrue($shift2->hasFlag('duplicate'), 'The new shift did not get the duplicate flag');
    }

    /**
     * @test
     */
    public function a_shift_is_duplicated_by_another_shift_when_clocking_inside_of_the_hours()
    {
        $shift1 = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('11:00:00', '16:00:00');

        $this->assertTrue($shift1->fresh()->hasFlag('duplicate'), 'The original shift did not get the duplicate flag');
        $this->assertTrue($shift2->hasFlag('duplicate'), 'The new shift did not get the duplicate flag');
    }

    /**
     * @test
     */
    public function a_shift_is_duplicated_by_another_shift_when_clocking_outside_of_the_hours()
    {
        $shift1 = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('08:00:00', '20:00:00'); // clock in is before, clock out is after, the reverse of inside

        $this->assertTrue($shift1->fresh()->hasFlag('duplicate'), 'The original shift did not get the duplicate flag');
        $this->assertTrue($shift2->hasFlag('duplicate'), 'The new shift did not get the duplicate flag');
    }

    /**
     * @test
     */
    public function a_shift_is_duplicated_by_another_shift_with_one_inside_and_one_outside()
    {
        $shift1 = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('08:00:00', '16:00:00'); // clock in outside, clock out inside
        $shift3 = $this->createDuplicateShift('12:00:00', '20:00:00'); // clock in inside, clock out outside

        $this->assertTrue($shift1->fresh()->hasFlag('duplicate'), 'The original shift did not get the duplicate flag');
        $this->assertTrue($shift2->hasFlag('duplicate'), 'The second shift (clock in outside) did not get the duplicate flag');
        $this->assertTrue($shift3->hasFlag('duplicate'), 'The third shift (clock out outside) did not get the duplicate flag');
    }

    /**
     * @test
     */
    public function a_shift_touching_but_not_crossing_the_edge_does_not_cause_duplicate_flags()
    {
        $shift1 = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('18:00:00', '19:00:00');
        $shift3 = $this->createDuplicateShift('06:00:00', '10:00:00');

        $this->assertFalse($shift1->fresh()->hasFlag('duplicate'), 'The original shift incorrectly has a duplicate flag');
        $this->assertFalse($shift2->hasFlag('duplicate'), 'The shift touching the clock out time incorrectly has a duplicate flag');
        $this->assertFalse($shift3->hasFlag('duplicate'), 'The shift touching the clock in time incorrectly has a duplicate flag');
    }

    /**
     * @param $in
     * @param $out
     * @return Shift
     */
    protected function createDuplicateShift($in, $out)
    {
        if (strlen($in) === 8) $in = date('Y-m-d') . ' ' . $in;
        if (strlen($out) === 8) $out = date('Y-m-d') . ' ' . $out;
        return factory(Shift::class)->create([
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'business_id' => $this->client->business_id,
            'checked_in_time' => $in,
            'checked_out_time' => $out,
        ]);
    }
}
