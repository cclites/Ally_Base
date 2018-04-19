<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Client;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\Resources\ClientCaregiver;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class ClientCaregiverController extends BaseController
{
    public function store(Request $request, Client $client)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $request->validate(['caregiver_id' => 'required|exists:caregivers,id']);
        $caregiver_id = $request->input('caregiver_id');

        $data = $request->validate([
            'caregiver_hourly_rate' => 'required|numeric',
            'caregiver_daily_rate' => 'nullable|numeric',
            'provider_hourly_fee' => 'required|numeric',
            'provider_daily_fee' => 'nullable|numeric',
        ]);

        // Force rates/fees to floats
        $data = array_map('floatval', $data);

        if ($client->caregivers()->syncWithoutDetaching([$caregiver_id => $data])) {
            $caregiver = $client->caregivers
                ->where('id', $caregiver_id)
                ->first();

            // Append # of future shifts scheduled for this client/caregiver
            $caregiver->scheduled_shifts_count = $caregiver->schedules()->where('client_id', $client->id)
                ->forClient($client)
                ->future($this->business()->timezone)
                ->count();

            $responseData = new ClientCaregiver($client, $caregiver);
            return new CreatedResponse('The caregiver assignment has been saved.', $responseData->toResponse(null));
        }

        return new ErrorResponse(500, 'Unable to save caregiver assignment.');
    }

    public function index(Client $client)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $caregivers = $client->caregivers->map(function ($caregiver) use ($client) {
            return (new ClientCaregiver($client, $caregiver))->toResponse(null);
        });

        return $caregivers->sortBy('name')->values()->all();
    }

    public function show(Client $client, Caregiver $caregiver)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        return new ClientCaregiver($client, $caregiver);
    }

    public function potentialCaregivers(Client $client)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $current_caregivers = $client->caregivers()->select('caregivers.id')->pluck('id');
        $excluded_caregivers = $client->excludedCaregivers()->select('caregiver_id')->pluck('caregiver_id');
        $excluded_caregivers = $excluded_caregivers->merge($current_caregivers);
        $caregivers = $this->business()
            ->caregivers()
            ->whereNotIn('caregivers.id', $excluded_caregivers->values())
            ->select('caregivers.id')
            ->get()
            ->map(function ($caregiver) {
                return [
                    'id' => $caregiver->id,
                    'name' => $caregiver->nameLastFirst
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();

        return response()->json($caregivers);
    }

    /**
     * @param Client $client
     * @return ErrorResponse|SuccessResponse
     */
    public function detachCaregiver(Client $client)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $caregiver = Caregiver::find(request('caregiver_id'));

        if ($caregiver->isClockedIn($client->id)) {
            return new ErrorResponse(400, $caregiver->name() . ' is clocked in for this client.');
        }

        // check for scheduled shifts and or clockin
        $check = $caregiver->schedules()
            ->forClient($client)
            ->future($this->business()->timezone)
            ->exists();

        if ($check) {
            $msg = $caregiver->name() . ' has future scheduled shifts for ' . $client->name() . ' and cannot be removed.';
            return new ErrorResponse(400, $msg);
        }

        $client->caregivers()->detach($caregiver->id);

        return new SuccessResponse($caregiver->name() . ' removed from ' . $client->name());
    }

    /**
     * Update rate information for future scheduled shifts for client/caregiver.
     *
     * @param Client $client
     * @return ErrorResponse|SuccessResponse
     */
    public function updateScheduleRates(Request $request, Client $client)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $request->validate(['caregiver_id' => 'required|exists:caregivers,id']);

        $caregiver = $client->caregivers
            ->where('id', $request->caregiver_id)
            ->first();

        $futureShifts = $caregiver->schedules()
            ->forClient($client)
            ->future($this->business()->timezone)
            ->update([
                'caregiver_rate' => $caregiver->pivot->caregiver_hourly_rate,
                'provider_fee' => $caregiver->pivot->provider_hourly_fee,
            ]);

        $request->validate(['caregiver_id' => 'required|exists:caregivers,id']);
        $caregiver_id = $request->input('caregiver_id');

        return new SuccessResponse($caregiver->name() . "'s rate was applied to all future schedules.");
    }
}
