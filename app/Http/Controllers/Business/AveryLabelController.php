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
        $sortBy    = $request->input( 'sort', 'lastname' );
        $sortOrder = $request->input( 'desc', false ) == 'true' ? 'desc' : 'asc';
        $search    = $request->input( 'search', null );
        $entity    = $request->input( 'userType', null );
        if( !in_array( $entity, [ 'caregiver', 'client' ] ) ) abort( 404 ); // better error return? this is not an ajax call but opening up a new window with a pdf..

        $query = User::with( $entity, "$entity.address" )
            ->where( 'role_type', $entity )
            ->forRequestedBusinesses();

        if ( $search ) {

            $query->where(function ($q) use ($search) {

                $q->where('users.email', 'LIKE', "%$search%")
                    ->orWhere('users.id', 'LIKE', "%$search%")
                    ->orWhere('users.firstname', 'LIKE', "%$search%")
                    ->orWhere('users.lastname', 'LIKE', "%$search%");
            });
        }

        // Default to active only, unless active is provided in the query string
        if ( $request->input( 'active', 1 ) !== null ) {

            $query->where( 'active', $request->input( 'active', 1 ) );
        }

        if ( $request->input( 'status' ) !== null ) {

            $query->where( 'status_alias_id', $request->input( 'status', null ) );
        }

        if ( $sortBy == 'lastname' || !$sortBy ) {

            $query->orderByRaw( "users.lastname $sortOrder, users.firstname $sortOrder" );
        } else {

            $query->orderBy( $sortBy, $sortOrder );
        }

        $users = array_chunk( $query->whereHas( "$entity.address" )->get()->map( function( $u ) use ( $entity ){

            return [

                'name'    => $u->name,
                'address' => $u->$entity->address
            ];
        })->toArray(), 3 );

        $pdf = PDF::loadView( 'avery-labels', compact( 'users' ) );
        return $pdf->stream( 'avery-labels.pdf' );
    }
}