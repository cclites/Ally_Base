<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\Shifts\RateFactory;
use Illuminate\Http\Request;
use App\Billing\ClientRate;
use App\Client;
use App\Http\Requests\UpdateClientRatesRequest;
use App\Billing\Validators\ClientRateValidator;
use App\Caregiver;

class ClientRatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Client $client)
    {
        $this->authorize('read', $client);
        
        $data = $client->rates()->ordered()->get();

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateClientRatesRequest $request
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function update(UpdateClientRatesRequest $request, Client $client)
    {
        $this->authorize('update', $client);
        $rates = $request->filtered();

        // Verify no negative provider fees
        foreach($rates as $rate) {
            foreach(['hourly', 'fixed'] as $type) {
                if (app(RateFactory::class)->hasNegativeProviderFee($client, $rate["client_${type}_rate"], $rate["caregiver_${type}_rate"])) {
                    return new ErrorResponse(400, 'The provider fee cannot be a negative number.');
                }
            }
        }
        
        \DB::beginTransaction();
        try {
            // Ensure all caregivers are attached to the client and
            // remove any caregivers that were previously attached
            // but no longer have any rates set.
            $caregivers = collect($rates)
                ->where('caregiver_id', '<>', null)
                ->pluck('caregiver_id');

            $unassignIds = $client->caregivers()
                ->whereNotIn('caregiver_id', $caregivers)
                ->get();

            foreach ($unassignIds as $caregiver) {
                if ($error = $this->getUnassignmentError($client, $caregiver)) {
                    return new ErrorResponse(400, $error);
                }
            }

            if (! empty($caregivers)) {
                $client->caregivers()->sync($caregivers);
            }

            // Add caregivers to the client's business location if not added already.
            foreach (Caregiver::whereIn('id', $caregivers)->get() as $caregiver) {
                $caregiver->ensureBusinessRelationship($client->business);
            }

            if ($client->syncRates($rates)) {
                $validator = new ClientRateValidator();
                if (! $validator->validate($client->fresh())) {
                    \DB::rollBack();
                    return new ErrorResponse(422, $validator->getErrorMessage());
                }

                \DB::commit();
                return new SuccessResponse('Client Rates saved successfully.', $client->fresh()->rates, '.');
            } 

            throw new \Exception();
        } catch (\Exception $ex) {
            \Log::error($ex->getMessage());
            \DB::rollBack();
            return new ErrorResponse(500, 'An unexpected error occurred.  Please try again.');
        }
    }

    /**
     * Check if a caregiver can be removed from a client.
     *
     * @param Client $client
     * @param Caregiver $caregiver
     * @return \Illuinate\Http\Response
     */
    public function canUnassign(Client $client, Caregiver $caregiver)
    {
        if ($error = $this->getUnassignmentError($client, $caregiver)) {
            return response()->json(['error' => 'Cannot not remove this rate row because it would unassign the caregiver.  ' . $error]);
        }

        return response()->json(['status' => 1]);
    }

    /**
     * Helper function to check if a caregiver can be unassigned
     * from a client.  Results in an error message for the reason
     * why they cannot be unassigned, or null if they can be.
     *
     * @param Client $client
     * @param Caregiver $caregiver
     * @return string|null
     */
    public function getUnassignmentError(Client $client, Caregiver $caregiver) : ?string
    {
        if ($caregiver->isClockedIn($client->id)) {
            return $caregiver->name() . ' cannot be unassigned because they are currently clocked in for this client.';
        }
        
        if ($caregiver->hasScheduledShifts($client)) {
            return $caregiver->name() . ' cannot be unassigned because they have future scheduled shifts for ' . $client->name() . '.';
        }
        
        return null;
    }
}
