<?php
namespace Tests\Bugs;

use App\Billing\Deposit;
use App\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToArrayErrorTest extends TestCase
{
    use RefreshDatabase;

    public function testDepositShiftsCanBeConvertedToArray()
    {
        $deposit = factory(Deposit::class)->create();
        $shifts = factory(Shift::class, 3)->create();
        $deposit->shifts()->attach($shifts->pluck('id')->toArray());

        $deposits = Deposit::has('shifts')->with('shifts')->first();
        $this->assertInternalType('array', $deposits->toArray());
    }
}
