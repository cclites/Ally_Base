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

    /**
     * 
     * The general algorithm of this import will be as follows:
     * - attempt to save the records
     * - return a collection of the items saved with their rowNumber as reference
     * - if a caregiver/client match could not be made, the front-end will allow for manual matching
     * - there will also be the ability to edit/change the imported row
     * - there will also 
     */
    public function handleImport()
    {
        $collection = collect();

        for( $rowNo = 2, $totalRows = $this->worksheet->getRowCount(); $rowNo <= $totalRows; $rowNo++ ) {
            // for every row..


            if ( empty( trim( $this->worksheet->getValue( 'Related To', $rowNo ) ) ) ) {
                // that isnt empty..

                continue;
            }

            $related_to = $this->worksheet->getValue( 'Related To', $rowNo );
            [ $caregiver, $client ] = $this->mapRelatedTo( $related_to );

            if( $caregiver == null || $client == null ){
                // if no client/caregiver match could be made, push the row reference and exit

                $collection->push([

                    'note' => null,
                    'row'  => $rowNo
                ]);

                continue;
            }

            $note = new Note([
                // create a note using the details of the row

                'business_id'        => $this->business->id,
                'caregiver_id'       => $caregiver->id,
                'client_id'          => $client->id,
                'title'              => $this->worksheet->getValue( 'Subject', $rowNo ),
                'body'               => $this->worksheet->getValue( 'Description', $rowNo ),
                'tags'               => $this->worksheet->getValue( 'Activity Tags', $rowNo ),
                'created_by'         => $this->worksheet->getValue( 'Created By', $rowNo ), // Jason said he would manually turn these into user id's
                'type'               => strtolower( $this->worksheet->getValue( 'Type', $rowNo ) ),
            ]);

            // and push the newly created object into the collection to return for the front-end response
            $collection->push([

                'note' => $note,
                'row'  => $rowNo
            ]);
        }

        return $collection;
    }

    /**
     * 1. break apart the provided string ( comes in a format of "name1, name2" )
     * 2. map the first name to a user in our system
     * 3. then determine if that is a client or caregiver
     * 4. repeat for second name
     * 5. return both
     */
    public function mapRelatedTo( $related_to )
    {
        $names = explode( ',', $related_to );

        foreach( $names as $name ){
      
          echo trim( $name );
        }
        return [ 'erik', 'emily' ];
    }

    /**
     * Find a caregiver record based on the name
     *
     * @param string $name
     * @return \App\Caregiver|null
     */
    function findCaregiver($name)
    {
        // Search by import_identifier
        if ($caregiver = $this->business->caregivers()->where('import_identifier', $name)->first()) {
            return $caregiver;
        }

        // Search by exact name, if one match
        $caregivers = $this->business->caregivers()->whereHas('user', function($q) use ($name) {
            $q->whereRaw('CONCAT(lastname, ", ", firstname) = ?', [trim($name)]);
        })->get();
        if ($caregivers->count() === 1) {
            return $caregivers->first();
        }

        return null;
    }

    /**
     * Find a client record based on the name
     *
     * @param string $name
     * @return \App\Client|null
     */
    function findClient($name)
    {
        // Search by import_identifier
        if ($client = $this->business->clients()->where('import_identifier', $name)->first()) {
            return $client;
        }

        // Search by exact name, if one match
        $clients = $this->business->clients()->whereHas('user', function($q) use ($name) {
            $q->whereRaw('CONCAT(lastname, ", ", firstname) = ?', [trim($name)]);
        })->get();
        if ($clients->count() === 1) {
            return $clients->first();
        }

        return null;
    }
}
