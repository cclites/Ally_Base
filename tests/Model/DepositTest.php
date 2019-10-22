<?php


namespace Tests\Model;


use App\Billing\Deposit;
use App\Billing\Gateway\AchExportFile;
use App\Events\DepositFailed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function a_deposit_emits_a_domain_event_when_marked_failed()
    {
        \Event::fake();

        $deposit = factory(Deposit::class)->create();
        $deposit->markFailed();

        \Event::assertDispatched(DepositFailed::class);
        $this->assertFalse($deposit->success);
    }

    /** @test */
    function ach_file_exports_string_sanitizer_should_only_allow_alpha_and_space()
    {
        $exporter = new AchExportFile();

        $this->assertEquals(
            'Normal Name',
            $exporter->sanitizeString('Normal Name')
        );

        $this->assertEquals(
            'ABCDEFGHIJKL  MNOPQRSTUVWXYZ',
            $exporter->sanitizeString('ABCDEFGHIJKL  ~!@#$%^&*()_+\\\'".,MNOPQRSTUVWXYZ1234567890')
        );

        $this->assertEquals(
            'abcdefghijklmnopqrstuvwxyz',
            $exporter->sanitizeString('"abc\'def!ghijklmnopqrst\'uvwxyz"')
        );

        $this->assertEquals(
            'John Doe Something',
            $exporter->sanitizeString('John Doe (Something)')
        );
    }
}