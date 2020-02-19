<?php

namespace App\Http\Controllers\Business;

use App\Http\Requests\CreateScheduleFreeFloatingNoteRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\ScheduleFreeFloatingNote;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleFreeFloatingNotesController extends BaseController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\CreateScheduleFreeFloatingNoteRequest $request
     */
    public function store( CreateScheduleFreeFloatingNoteRequest $request )
    {
        $data = $request->validated();
        $data[ 'start_date' ] = Carbon::parse( $request[ 'start_date' ] );
        if( ScheduleFreeFloatingNote::create( $data ) ) return new SuccessResponse( 'Successfully created schedule note!', $request->validated() );
        else return new ErrorResponse( 500, 'Error creating schedule note, please try again!' );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\CreateScheduleFreeFloatingNoteRequest $request
     */
    public function update( CreateScheduleFreeFloatingNoteRequest $request, ScheduleFreeFloatingNote $schedule_free_floating_note )
    {
        $data = $request->validated();
        $data[ 'start_date' ] = Carbon::parse( $request[ 'start_date' ] );
        if( $schedule_free_floating_note->update( $data ) ) return new SuccessResponse( 'Successfully saved schedule note!', $request->validated() );
        else return new ErrorResponse( 500, 'Error saving schedule note, please try again!' );
    }

    /**
     * Kill the resource
     *
     * @param  \Illuminate\Http\CreateScheduleFreeFloatingNoteRequest $request
     */
    public function destroy( ScheduleFreeFloatingNote $schedule_free_floating_note )
    {
        if( $schedule_free_floating_note->delete() ) return new SuccessResponse( 'Successfully deleted schedule note!' );
        else return new ErrorResponse( 500, 'Error deleting schedule note, please try again!' );
    }
}
