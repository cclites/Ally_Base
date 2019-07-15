<?php

namespace Tests;

use App\Billing\ClientAuthorization;
use Carbon\Carbon;

/**
 * Trait CreatesClientInvoiceResources
 * Note: This trait requires a $client property to be defined
 *
 * @package Tests
 *
 *
 */
trait CreatesClientAuthorizations
{
    /**
     * Helper to create a ClientAuthorization.
     *
     * @param array $data
     * @return ClientAuthorization
     */
    public function createClientAuth(array $data) : ClientAuthorization
    {
        return factory(ClientAuthorization::class)->create(array_merge([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'units' => 0.0,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_MONTHLY,
            'effective_start' => Carbon::now()->subYears(1)->toDateString(),
        ], $data));
    }
}