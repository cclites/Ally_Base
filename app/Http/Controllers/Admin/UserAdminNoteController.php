<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAdminNoteRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\User;
use App\UserAdminNote;
use Illuminate\Http\Request;

class UserAdminNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request )
    {
        if( !is_admin() ) abort( 404 );

        $notes = UserAdminNote::with( 'creator' )->forSubject( $request->subject_user_id )->get();
        return response()->json( $notes );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( UserAdminNoteRequest $request )
    {
        if( !$note = UserAdminNote::create( $request->validated() ) ) return new ErrorResponse( 500, 'unable to create admin note!' );
        $note = UserAdminNote::with( 'creator' )->find( $note->id );
        return new SuccessResponse( 'Successfully created admin note!', $note );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( UserAdminNote $adminNote )
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update( UserAdminNoteRequest $request, UserAdminNote $adminNote )
    {
        $adminNote->body = $request->body;
        if( !$adminNote->save() ) return new ErrorResponse( 500, 'unable to update admin note!' );
        return new SuccessResponse( 'Successfully updated admin note!', $adminNote->fresh() );

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( UserAdminNote $adminNote )
    {
        if( !is_admin() ) abort( 403 );

        if( !$adminNote->delete() ) return new ErrorResponse( 500, 'unable to delete admin note!' );
        return new SuccessResponse( 'Successfully deleted admin note!' );
    }
}
