<?php

namespace App\Responses\Resources;

use App\Shifts\AllyFeeCalculator;
use App\Shifts\RateFactory;
use Illuminate\Contracts\Support\Responsable;

class CaregiverClient extends ClientCaregiver
{

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        $rates = [
            'hourly' => app(RateFactory::class)->getRatesForClientCaregiver($this->client, $this->caregiver, false, $this->pivot),
            'fixed' => app(RateFactory::class)->getRatesForClientCaregiver($this->client, $this->caregiver, true, $this->pivot),
        ];

        $pivot = $this->pivot;

        $response = array_merge(
            $this->client->toArray(),
            compact('rates', 'pivot')
        );

        return $response;
    }
}
