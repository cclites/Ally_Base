<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\Worksheet;
use App\Business;
use App\Note;

use Illuminate\Http\Request;

class NoteImportController extends Controller
{

    /**
     * @var \App\Imports\Worksheet
     */
    public $worksheet;

    /**
     * @var \App\Business
     */
    public $business;

    public function view()
    {
        return view( 'admin.import.notes' );
    }

    public function process(Request $request)
    {
        $request->validate([

            'business_id' => 'required|exists:businesses,id',
            'file'        => 'required|file',
        ]);

        $this->business = Business::findOrFail( $request->business_id );
        $file = $request->file( 'file' )->getPathname();

        $this->worksheet = new Worksheet( $file);

        return $this->handleImport();
    }

    protected function handleImport()
    {
        $collection = collect();

        for( $i = 2, $n = $this->worksheet->getRowCount(); $i <= $n; $i++ ) {
            // for every row..


            if ( empty( trim( $this->worksheet->getValue( 'Related To', $i ) ) ) ) {
                // that isnt empty..

                continue;
            }

            $note = new Note([
                // create a note using the details of the row

                'business_id'        => $this->business->id,
                'caregiver_id'       => ( $caregiver = $this->findCaregiver( $caregiverName ) ) ? $caregiver->id : null,
                'client_id'          => ( $client = $this->findClient( $clientName ) ) ? $client->id : null,
                'title'              => $checkIn->toDateTimeString(),
                'body'               => $checkOut->toDateTimeString(),
                'tags'               => $this->getCaregiverRate($rowNo, $overtime),
                'created_by'         => $this->getProviderFee($rowNo, $overtime),
                'referral_source_id' => $expenses ? $this->getMileage($rowNo) : 0,
                'type'               => $expenses ? $this->getOtherExpenses($rowNo) : 0,
                'call_direction'     => ($overtime) ? 'overtime' : 'default',
            ]);


            $array = [

                'note' => $note
            ];

            // and push the newly created object into the collection to return for the front-end response
            $collection->push( $array );
        }

        return $collection;
    }


    /**
     * Add a shift (used by addRegularShift and addOvertimeShift)
     *
     * @param \Illuminate\Support\Collection $collection
     * @param $rowNo
     * @param \Carbon\Carbon $checkIn
     * @param $hours
     * @param bool $overtime
     * @param bool $expenses
     * @return \App\Shift
     */
    function _addShift(Collection $collection, $rowNo, Carbon $checkIn, $hours, $overtime = false, $expenses = true)
    {
        $caregiverName = $this->getCaregiverName($rowNo);
        $clientName = $this->getClientName($rowNo);
        $checkOut = $checkIn->copy()->addSeconds(round($hours * 3600));

        $shift = new Shift([
            'business_id' => $this->business->id,
            'caregiver_id' => ($caregiver = $this->findCaregiver($caregiverName)) ? $caregiver->id : null,
            'client_id' => ($client = $this->findClient($clientName)) ? $client->id : null,
            'checked_in_time' => $checkIn->toDateTimeString(),
            'checked_out_time' => $checkOut->toDateTimeString(),
            'caregiver_rate' => $this->getCaregiverRate($rowNo, $overtime),
            'provider_fee' => $this->getProviderFee($rowNo, $overtime),
            'mileage' => $expenses ? $this->getMileage($rowNo) : 0,
            'other_expenses' => $expenses ? $this->getOtherExpenses($rowNo) : 0,
            'hours_type' => ($overtime) ? 'overtime' : 'default',
            'caregiver_comments' => $this->getComments($rowNo),
        ]);

        $array = [
            'shift' => $shift,
            'identifiers' => [
                'caregiver_name' => $caregiverName,
                'client_name' => $clientName,
            ]
        ];

        $collection->push($array);
        return $shift;
    }
}
