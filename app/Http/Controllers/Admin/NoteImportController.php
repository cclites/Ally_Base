<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\Worksheet;
use App\Business;
use App\User;
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
            $names = explode( ',', $related_to );

            $caregiver_id  = null;
            $caregiverName = '';
            $client_id     = null;
            $clientName    = '';
            foreach( $names as $name ){

                $user = User::whereRaw( 'CONCAT( firstname, " ", lastname ) = ?', [ trim( $name ) ])
                    ->forBusinesses([ $this->business->id ])
                    ->first();

                \Log::info( 'Found User!: ' . $user );

                if( !empty( $user ) ){

                    \Log::info( 'With Type: ' . $user->role_type );

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
                            // no default, leave the information null

                            break;
                    }
                }
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
            ]);

            // and push the newly created object into the collection to return for the front-end response
            $collection->push([

                'note'        => $note,
                'rowNo'       => $rowNo,
                'identifiers' => [

                    'caregiver_name' => $caregiverName,
                    'client_name'    => $clientName
                ]
            ]);
        }

        return $collection;
    }
}
