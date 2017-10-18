<?php
namespace App\Http\Controllers\Business;

use App\Client;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class CaregiverLocationController extends BaseController
{
    const DEFAULT_DISTANCE = 10;
    const DEFAULT_UNITS = 'mi';

    public function report()
    {
        $clients = $this->business()->clients()->with('user', 'evvAddress')->get();
        return view('business.caregivers.distance_report', compact('clients'));
    }

    public function distances(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'distance' => 'nullable|numeric',
            'units' => 'nullable|in:mi,km',
            'assigned_only' => 'nullable|boolean'
        ]);

        if (empty($data['units'])) $data['units'] = self::DEFAULT_UNITS;
        if (empty($data['distance'])) $data['distance'] = self::DEFAULT_DISTANCE;

        $client = Client::with('evvAddress')->find($data['client_id']);
        if ($this->business()->id != $client->business_id) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }
        if (!$client->evvAddress) {
            return new ErrorResponse(400, 'This client does not have service address on file.');
        }

        if (empty($data['assigned_only'])) {
            $query = $this->business()->caregivers();
        }
        else {
            $query = $client->caregivers();
        }

        $caregivers = $query->with(['user', 'addresses'])
            ->whereHas('addresses', function($q) {
                $q->where('type', 'home');
            })
            ->get();

        $list = collect();
        foreach($caregivers as $caregiver) {
            $address = $caregiver->addresses->where('type', 'home')->first();
            $distance = $address->distanceToAddress($client->evvAddress, $data['units']);
            if ($distance <= $data['distance']) {
                $list->push([
                    'id' => $caregiver->id,
                    'firstname' => $caregiver->firstname,
                    'lastname' => $caregiver->lastname,
                    'name' => $caregiver->nameLastFirst(),
                    'distance' => $distance,
                    'units' => $data['units'],
                    'address' => $address,
                ]);
            }
        }

        if (!$list->count()) {
            return new ErrorResponse(400, 'No caregivers found in the search radius.');
        }

        return new SuccessResponse($list->count() . ' caregivers have been found.', $list->sortBy('distance')->values()->toArray());
    }
}