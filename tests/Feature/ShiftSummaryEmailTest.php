<?php

namespace Tests\Feature;

use App\Billing\Service;
use App\Client;
use App\Mail\AssignedTaskEmail;
use App\Mail\ClientShiftSummaryEmail;
use App\Shift;
use Carbon\Carbon;
use Tests\CreatesShifts;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesBusinesses;
use App\Console\Commands\CronShiftSummaryEmails;

class ShiftSummaryEmailTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses, CreatesShifts;

    public function setUp()
    {
        parent::setUp();

        $this->createBusinessWithUsers();

        // turn setting on for main business
        $this->business->update(['shift_confirmation_email' => true]);

        // turn setting on for main client
        $this->client->update(['receive_summary_email' => true]);

        // create at least 2 shifts for the main client
        $this->service = factory(Service::class)->create([
            'chain_id' => $this->client->business->businessChain->id,
            'default' => true
        ]);

        $lastMonday = Carbon::parse('last week')->startOfWeek();
        $this->shift1 = $this->createShift($lastMonday, '08:00', 4, [
            'status' => Shift::WAITING_FOR_CONFIRMATION,
            'client_id' => $this->client->id,
        ]);
        $this->shift2 = $this->createShift($lastMonday->copy()->addDays(1), '08:00', 4, [
            'status' => Shift::WAITING_FOR_CONFIRMATION,
            'client_id' => $this->client->id,
        ]);
    }

    /** @test */
    function it_should_not_send_emails_if_the_business_has_the_feature_turned_off()
    {
        $this->business->update(['shift_confirmation_email' => false]);

        \Mail::fake();

        \Mail::assertNothingQueued();

        (new CronShiftSummaryEmails())->handle();

        \Mail::assertNothingQueued();
    }

    /** @test */
    public function it_should_only_send_emails_to_clients_that_have_the_setting_turned_on()
    {
        \Mail::fake();

        $otherClient = factory(Client::class)->create(['business_id' => $this->business->id]);
        $this->createShift(Carbon::parse('last week')->startOfWeek(), '08:00', 4, [
            'status' => Shift::WAITING_FOR_CONFIRMATION,
            'client_id' => $otherClient->id,
        ]);

        \Mail::assertNothingQueued();

        (new CronShiftSummaryEmails())->handle();

        \Mail::assertQueued(ClientShiftSummaryEmail::class, function ($mail) {
            return $mail->client->id == $this->client->id;
        });

        \Mail::assertNotQueued(ClientShiftSummaryEmail::class, function ($mail) use ($otherClient) {
            return $mail->client->id == $otherClient->id;
        });
    }

    /** @test */
    function it_should_not_contain_shifts_from_the_current_pay_period()
    {
        \Mail::fake();

        $badShift = $this->createShift(Carbon::now()->startOfWeek(), '01:00', 4, [
            'status' => Shift::WAITING_FOR_CONFIRMATION,
            'client_id' => $this->client->id,
        ]);

        \Mail::assertNothingQueued();

        (new CronShiftSummaryEmails())->handle();

        \Mail::assertQueued(ClientShiftSummaryEmail::class, function ($mail) use ($badShift) {
            if ($mail->client->id !== $this->client->id) {
                return false;
            }

            $shiftIds = $mail->shifts->pluck('id');
            return $shiftIds->contains($this->shift1->id)
                && $shiftIds->contains($this->shift2->id)
                && ! $shiftIds->contains($badShift->id);
        });
    }

    /** @test */
    function it_should_calculate_shift_dates_based_on_the_business_timezone()
    {
        app('settings')->set($this->business, 'timezone', 'America/Los_Angeles');
        $this->business->update(['timezone' => 'America/Los_Angeles']);

        \Mail::fake();

        $badShift = $this->createShift(Carbon::now()->startOfWeek(), '01:01', 4, [
            'status' => Shift::WAITING_FOR_CONFIRMATION,
            'client_id' => $this->client->id,
        ]);

        \Mail::assertNothingQueued();

        (new CronShiftSummaryEmails())->handle();

        \Mail::assertQueued(ClientShiftSummaryEmail::class, function ($mail) use ($badShift) {
            if ($mail->client->id !== $this->client->id) {
                return false;
            }

            $shiftIds = $mail->shifts->pluck('id');
            return $shiftIds->contains($this->shift1->id)
                && $shiftIds->contains($this->shift2->id)
                && ! $shiftIds->contains($badShift->id);
        });
    }
}
