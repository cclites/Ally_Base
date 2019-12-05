<?php

namespace App\Http\Controllers\Business;

use App\User;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class AveryLabelController extends BaseController
{
    /**
     * Get a list of addresses for the selected users in Avery 5160 format
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $entity = $request->input( 'userType', null );
        if( !in_array( $entity, [ 'caregiver', 'client' ] ) ) abort( 404 );

        $query = User::with( $entity, "$entity.address" )
            ->whereHas( "$entity.address" )
            ->where( 'role_type', $entity )
            ->forRequestedBusinesses();

        if( $search = $request->input( 'search' ) ) {

            $query->where( function ( $q ) use ( $search ) {

                $q->where( 'users.email', 'LIKE', "%$search%")
                    ->orWhere('users.id', 'LIKE', "%$search%")
                    ->orWhere('users.firstname', 'LIKE', "%$search%")
                    ->orWhere('users.lastname', 'LIKE', "%$search%");
            });
        }

        if( $clientType = $request->input( 'client_type' ) ) {

            $query->whereHas( $entity, function( $q ) use ( $clientType ){

                $q->where( "client_type", $clientType );
            });
        }


        if( $caseManagerId = $request->input( 'case_manager_id' ) ) {

            $query->whereHas( "$entity.caseManager", function ( $q ) use ( $caseManagerId ) {

                $q->where( 'id', $caseManagerId );
            });
        }

        if ( $request->input( 'active', 1 ) !== null ) {

            $query->where('active', $request->input( 'active', 1 ) );
        }

        if( $status = $request->input( 'status' ) ) {

            $query->where( 'status_alias_id', $status );
        }

        $pages = array_chunk( array_chunk( $query->get()->map( function( $u ) use ( $entity ){

            return [

                'name'    => $u->name,
                'address' => $u->$entity->address
            ];
        })->toArray(), 3 ), 10 );

        $pdf = PDF::loadView( 'avery-labels', [ 'pages' => $pages, 'leftmargin' => $request->input( 'leftmargin' ), 'topmargin' => $request->input( 'topmargin' ) ] );
        return $pdf->stream( 'avery-labels.pdf' );
    }
}