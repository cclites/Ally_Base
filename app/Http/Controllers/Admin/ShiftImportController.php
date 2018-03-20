<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Imports\ImportManager;
use App\Responses\CreatedResponse;
use App\Responses\SuccessResponse;
use App\Shift;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShiftImportController extends Controller
{
    public function index()
    {
        return view('admin.import.shifts');
    }

    public function process(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'provider' => 'required|string',
            'file' => 'required|file',
        ]);

        $business = Business::findOrFail($request->business_id);
        $file = $request->file('file')->getPathname();

        $import = ImportManager::open($request->provider, $business, $file);
        return $import->handle();
    }

    public function store(Request $request)
    {
        $request->validate([
            'shifts.*.business_id' => 'required|exists:businesses,id',
            'shifts.*.caregiver_id' => 'required|exists:caregivers,id',
            'shifts.*.client_id' => 'required|exists:clients,id',
            'shifts.*.checked_in_time' => 'required|date',
            'shifts.*.checked_out_time' => 'required|date',
            'shifts.*.caregiver_rate' => 'required|numeric|max:1000|min:0',
            'shifts.*.provider_fee' => 'required|numeric|max:1000|min:0',
            'shifts.*.mileage' => 'required|numeric|max:1000|min:0',
            'shifts.*.other_expenses' => 'required|numeric|max:1000|min:0',
            'shifts.*.hours_type' => 'required|in:default,overtime,holiday',
        ]);

        $shifts = collect();
        foreach($request->shifts as $data) {
            $shift = new Shift($data);
            $shift->status = Shift::WAITING_FOR_AUTHORIZATION;
            if ($shift->save()) {
                $shifts->push($shift);
            }
        }

        return new CreatedResponse("{$shifts->count()} shifts created.");
    }

    public function storeClientMapping(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:clients,id',
            'name' => 'required|string'
        ]);

        $client = Client::find($request->id);

        // Clear existing mappings for name and business
        Client::where('business_id', $client->business_id)
            ->where('import_identifier', $request->name)
            ->update(['import_identifier' => null]);

        // Add mapping
        $client->update(['import_identifier' => $request->name]);
        return new SuccessResponse('Client ' . $client->id . ' has been mapped to ' . $request->name);
    }

    public function storeCaregiverMapping(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:caregivers,id',
            'name' => 'required|string'
        ]);

        $caregiver = Caregiver::find($request->id);
        $business = $caregiver->businesses->first();

        // Clear existing mappings for name and business
        $business->caregivers()
                 ->where('import_identifier', $request->name)
                 ->update(['import_identifier' => null]);

        // Add mapping
        $caregiver->update(['import_identifier' => $request->name]);
        return new SuccessResponse('Caregiver ' . $caregiver->id . ' has been mapped to ' . $request->name);
    }
}
