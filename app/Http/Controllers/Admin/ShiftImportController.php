<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Billing\GatewayTransaction;
use App\Import;
use App\Imports\ImportManager;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Data\ScheduledRates;
use App\Shift;
use App\Shifts\Data\CaregiverClockoutData;
use App\Shifts\Data\ClockData;
use App\Shifts\ShiftFactory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\ShiftFlagsCouldChange;

class ShiftImportController extends Controller
{
    public function view()
    {
        return view('admin.import.shifts');
    }

    public function getDescription($provider)
    {
        // Some file that will always exist
        $dummyFile = public_path('css/style.css');

        $import = ImportManager::open($provider, new Business(), $dummyFile);
        return response()->json(['provider' => $provider, 'description' => $import->getDescription()]);
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
            'name' => 'required|string|max:16',
            'shifts.*.business_id' => 'required|exists:businesses,id',
            'shifts.*.caregiver_id' => 'required|exists:caregivers,id',
            'shifts.*.client_id' => 'required|exists:clients,id',
            'shifts.*.checked_in_time' => 'required|date',
            'shifts.*.checked_out_time' => 'required|date',
            'shifts.*.caregiver_rate' => 'required|numeric|max:1000|min:0',
            'shifts.*.provider_fee' => 'required|numeric|max:1000|min:0',
            'shifts.*.mileage' => 'required|numeric|max:9999|min:0',
            'shifts.*.other_expenses' => 'required|numeric|max:1000|min:-1000',
            'shifts.*.hours_type' => 'required|in:default,overtime,holiday',
        ]);



        /** @var Shift[]|\Illuminate\Support\Collection $shifts */
        $shifts = collect();
        foreach($request->shifts as $data) {

            $client = Client::find($data['client_id']);
            $caregiver = Caregiver::find($data['caregiver_id']);
            $clockIn = new ClockData(Shift::METHOD_IMPORTED, $data['checked_in_time']);
            $clockOut = new ClockData(Shift::METHOD_IMPORTED, $data['checked_out_time']);
            $totalRates = add($data['caregiver_rate'], $data['provider_fee']);
            $allyFee = $client->getAllyFee($totalRates, false);
            $clientRate = add($totalRates, $allyFee);
            $rates = new ScheduledRates(
                $clientRate,
                $data['caregiver_rate'],
                false, // fixed rates not yet supported
                $data['hours_type']
            );

            $factory = ShiftFactory::withoutSchedule(
                $client,
                $caregiver,
                $clockIn,
                $clockOut,
                $rates,
                Shift::WAITING_FOR_AUTHORIZATION
            );

            $clockOutData = new CaregiverClockoutData(
                $clockOut,
                $data['mileage'] ?? 0.0,
                $data['other_expenses'] ?? 0.0
            );

            $shift = $factory->make($clockOutData);
            $shifts->push($shift);
        }

        // Set expectations
        $business = $shifts->first()->business;
        $caregivers = $business->chain->caregivers;
        $clients = $business->clients;

        // Additional validations
        foreach($shifts as $index => $shift) {

            $shiftName = 'for' . $shift->client->name . ' at ' . $shift->checked_in_time . ' UTC';

            if ($shift->checked_in_time > $shift->checked_out_time) {
                return new ErrorResponse(400, 'The shift ' . $shiftName . ' has a greater checked_in_time than checked_out_time');
            }

            if ($shift->business_id != $business->id) {
                return new ErrorResponse(400, 'The shift ' . $shiftName . ' does not belong to the same business.');
            }

            if (!$clients->where('id', $shift->client_id)->count()) {
                return new ErrorResponse(400, 'The shift ' . $shiftName . ' has a client that does not belong to the business.');
            }

            if (!$caregivers->where('id', $shift->caregiver_id)->count()) {
                return new ErrorResponse(400, 'The shift ' . $shiftName . ' has a caregiver that does not belong to the business chain.');
            }

            if ($shift->duration() > 24) {
                return new ErrorResponse(400, 'The shift ' . $shiftName . ' has a duration greater than 24 hours.');
            }

            if ($shift->hasDuplicate()) {
                return new ErrorResponse(400, 'The shift ' . $shiftName . ' exists in the database.');
            }

            // Check for duplicates in current import
            $filter = $shifts->filter(function($item) use ($shift) {
                 return $item->checked_in_time == $shift->checked_in_time
                     && $item->client_id == $shift->client_id
                     && $item->caregiver_id == $shift->caregiver_id;
            });
            if ($filter->count() > 1) {
                return new ErrorResponse(400, 'The shift ' . $shiftName . ' has a duplicate in the import.');
            }

        }

        // Save shifts and create import record
        \DB::beginTransaction();
        $import = Import::create([
            'name' => $request->name,
            'type' => 'shift',
            'user_id' => \Auth::id()
        ]);
        foreach($shifts as $shift) {
            $import->shifts()->save($shift);
            event(new ShiftFlagsCouldChange($shift));
        }
        \DB::commit();

        return new CreatedResponse("{$import->shifts()->count()} shifts created for {$business->name}.");
    }

    public function storeClientMapping(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:clients,id',
            'name' => 'required|string'
        ]);

        $client = Client::findOrFail($request->id);

        // Clear existing mappings for name and business
        Client::where('business_id', $client->business_id)
            ->where('import_identifier', $request->name)
            ->update(['import_identifier' => null]);

        // Add mapping
        // We don't use $client->update() here because of the previous update above may leave $client in an outdated state
        Client::where('id', $client->id)
              ->update(['import_identifier' => $request->name]);

        return new SuccessResponse('Client ' . $client->id . ' has been mapped to ' . $request->name);
    }

    public function storeCaregiverMapping(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:caregivers,id',
            'name' => 'required|string'
        ]);

        $caregiver = Caregiver::findOrFail($request->id);
        $business = $caregiver->businesses->first();

        // Clear existing mappings for name and business
        $business->caregivers()
                 ->where('import_identifier', $request->name)
                 ->update(['import_identifier' => null]);

        // Add mapping
        $caregiver->update(['import_identifier' => $request->name]);
        return new SuccessResponse('Caregiver ' . $caregiver->id . ' has been mapped to ' . $request->name);
    }

    public function index(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            return Import::orderBy('id', 'DESC')->get();
        }
        return view('admin.import.report');
    }

    public function show(Import $import)
    {
        return $import->load('shifts');
    }

    public function destroy(Import $import)
    {
        // Protection against deleting old imports.
        if ($import->created_at->diffInDays() > 1) {
            return new ErrorResponse(400, 'This import is too old to be rolled back automatically.');
        }

        $import->delete();
        return new SuccessResponse('Import has been rolled back.');
    }

}
