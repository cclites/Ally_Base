<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\Worksheet;
use App\Shifts\ShiftFactory;
use App\Business;
use App\User;
use App\Client;
use App\Import;
use App\Caregiver;
use App\Note;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;

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
            $names = explode( ',', $related_to );

            $caregiver_id  = null;
            $client_id     = null;

            $caregiverName = null;
            $clientName    = null;

            foreach( $names as $name ){

                // Search by import_identifier
                if ( $client = $this->business->clients()->where( 'import_identifier', trim( $name ) )->first() ) {

                    $client_id  = $client->id;
                    $clientName = trim( $name );
                    continue;
                }

                // Search by import_identifier
                if ( $caregiver = $this->business->caregivers()->where( 'import_identifier', trim( $name ) )->first() ) {

                    $caregiver_id  = $caregiver->id;
                    $caregiverName = trim( $name );
                    continue;
                }

                if( $user = User::whereRaw( 'CONCAT( firstname, " ", lastname ) = ?', [ trim( $name ) ])->forBusinesses([ $this->business->id ])->first() ){

                    switch( $user->role_type ){

                        case 'client':

                            $client_id  = $user->id;
                            $clientName = trim( $name );
                            break;
                        case 'caregiver':

                            $caregiver_id  = $user->id;
                            $caregiverName = trim( $name );
                            break;
                        default:
                            break;
                    }
                }
            }

            if( empty( $caregiverName ) && !empty( $clientName ) ) $caregiverName = $this->takeUnmatchedName( $names, $clientName );

            if( !empty( $caregiverName ) && empty( $clientName ) ) $clientName = $this->takeUnmatchedName( $names, $caregiverName );

            if( empty( $caregiverName ) && empty( $clientName ) ){
                // this is a wierd position.. neither matched.. just arbitrarily assign the names to different spots..

                $clientName    = $names[ 0 ];
                $caregiverName = $names[ 1 ];
            }


            $note = new Note([
                // create a note using the details of the row

                'business_id'  => $this->business->id,
                'caregiver_id' => empty( $caregiver_id ) ? null : $caregiver_id,
                'client_id'    => empty( $client_id    ) ? null : $client_id,
                'title'        => $this->worksheet->getValue( 'Subject', $rowNo ),
                'body'         => $this->worksheet->getValue( 'Description', $rowNo ),
                'tags'         => $this->worksheet->getValue( 'Activity Tags', $rowNo ),
                'created_by'   => $this->worksheet->getValue( 'Created By', $rowNo ), // Jason said he would manually turn these into user id's
                'type'         => strtolower( $this->worksheet->getValue( 'Type', $rowNo ) ),
                'rowNo'        => $rowNo
            ]);

            // and push the newly created object into the collection to return for the front-end response
            $collection->push([

                'note'        => $note,
                'identifiers' => [

                    'caregiver_name' => $caregiverName,
                    'client_name'    => $clientName
                ]
            ]);
        }

        return $collection;
    }

    public function takeUnmatchedName( $names, $matchedName )
    {
        $untaken = array_values( array_filter( $names, function( $value ) use ( $matchedName ){

            return $value != $matchedName;
        }));

        return $untaken[ 0 ];
    }


    public function store(Request $request)
    {
        $request->validate([

            'name'                 => 'required|string|max:16',
            'notes.*.business_id'  => 'required|exists:businesses,id',
            'notes.*.caregiver_id' => 'required|exists:caregivers,id',
            'notes.*.client_id'    => 'required|exists:clients,id',
            'notes.*.created_by'   => 'required|exists:users,id',
            'notes.*.body'         => 'required',
            'notes.*.title'        => 'nullable',
            'notes.*.tags'         => 'nullable',
            'notes.*.type'         => 'nullable',
            'notes.*.rowNo'        => 'nullable'
        ]);

        /** @var Notes[]|\Illuminate\Support\Collection $notes */
        $notes = collect();
        foreach( $request->notes as $data ) {

            $createdBy = User::find($data['created_by']);
            $client    = Client::find($data['client_id']);
            $caregiver = Caregiver::find($data['caregiver_id']);

            $note = factory( Note::class )->make([

                'business_id'  => $data[ 'business_id' ],
                'caregiver_id' => $data[ 'caregiver_id' ],
                'client_id'    => $data[ 'client_id' ],
                'body'         => $data[ 'body' ],
                'title'        => $data[ 'title' ],
                'tags'         => $data[ 'tags' ],
                'created_by'   => $data[ 'created_by' ],
                'type'         => $data[ 'type' ]
            ]);

            $note[ 'rowNo' ] = $data[ 'rowNo' ];

            $notes->push( $note );
        }

        // Set expectations
        $business   = $notes->first()->business;
        $caregivers = $business->chain->caregivers;
        $clients    = $business->clients;

        // Additional validations
        foreach( $notes as $index => $note ) {

            if ( $note->business_id != $business->id ) {
                return new ErrorResponse(400, 'Note from excel row: ' . $note[ 'rowNo' ] . ' doesn\'t belong to the same business.' );
            }

            if (!$clients->where('id', $note->client_id)->count()) {
                return new ErrorResponse(400, 'Note from excel row: ' . $note[ 'rowNo' ] . ' has a client that does not belong to the business.');
            }

            if (!$caregivers->where('id', $note->caregiver_id)->count()) {
                return new ErrorResponse(400, 'Note from excel row: ' . $note[ 'rowNo' ] . ' has a caregiver that does not belong to the business chain.');
            }

            // Check for duplicates in current import
            $filter = $notes->filter(function($item) use ($note) {
                 return $item->body == $note->body
                     && $item->client_id == $note->client_id
                     && $item->caregiver_id == $note->caregiver_id;
            });
            if ($filter->count() > 1) {
                return new ErrorResponse(400, 'Note from excel row: ' . $note[ 'rowNo' ] . ' has a duplicate in the import.');
            }
        }

        // Save shifts and create import record
        \DB::beginTransaction();

        $import = Import::create([

            'name'    => $request->name,
            'type'    => 'note',
            'user_id' => \Auth::id()
        ]);

        foreach( $notes as $note ) {

            unset( $note[ 'rowNo' ] );
            $import->notes()->save( $note );
        }
        \DB::commit();

        return new CreatedResponse("{$import->notes()->count()} notes created for {$business->name}.");
    }

    // this is the same functionality as the shift import.. probably doesnt need to be its own function.. could just
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

    // this is the same functionality as the shift import.. probably doesnt need to be its own function.. could just
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
}
