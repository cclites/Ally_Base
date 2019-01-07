<?php
namespace Tests\Model;

use App\Billing\Service;
use App\BusinessChain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function a_service_can_be_created()
    {
        $service = factory(Service::class)->create();

        $this->assertGreaterThan(0, $service->id);
    }

    function a_default_service_for_a_chain_can_be_retrieved()
    {
        $chainA = factory(BusinessChain::class)->create();
        $chainB = factory(BusinessChain::class)->create();

        // Create irrelevant services
        factory(Service::class)->create(['chain_id' => $chainB->id, 'default' => true]);
        factory(Service::class)->create(['chain_id' => $chainA->id]);

        $service = factory(Service::class)->create(['chain_id' => $chainA->id, 'default' => true]);

        $retrieved = Service::getDefault($chainA->id);
        $this->assertEquals($service->name, $retrieved->name);
    }
}