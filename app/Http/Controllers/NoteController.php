<?php

namespace App\Http\Controllers;

use App\Note;
use App\OfficeUser;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\CreateNoteRequest;
use Illuminate\Http\Response;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('notes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('notes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateNoteRequest $request)
    {
        if ($note = Note::create(array_merge($request->validated(), [
            'created_by' => auth()->id(),
        ]))) {

            if ($request->input('modal')) {
                return new CreatedResponse('Note Created', $note->load('creator', 'client', 'caregiver', 'prospect', 'referral_source'));
            }

            return new CreatedResponse('Note Created', [], '/notes');
        }
        
        return new ErrorResponse(500, 'The note could not be created.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function update(CreateNoteRequest $request, Note $note)
    {
        if ($note->update($request->validated())) {
            if ($request->input('modal')) {
                return new SuccessResponse('Note has been updated.', $note->fresh()->load('creator', 'client', 'caregiver', 'prospect', 'referral_source'));
            }

            return new SuccessResponse('Note has been updated');
        }

        return new ErrorResponse(500, 'The note could not be created.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function destroy(Note $note)
    {
        if ($note->delete()) {
            return new SuccessResponse('Note deleted.', $note->toArray());
        }

        return new ErrorResponse(500, 'The note could not be deleted.');
    }

    /**
     * Handle search filters.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request): \Illuminate\Http\Response
    {
        $notes = Note::forRequestedBusinesses()
            ->with('caregiver', 'client', 'prospect', 'referral_source')
            ->when($request->filled('start_date'), function ($query) use ($request) {
                return $query->where('created_at', '>=', Carbon::parse($request->start_date)->subDay());
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                return $query->where('created_at', '<=', Carbon::parse($request->end_date)->addDay());
            })
            ->when($request->filled('caregiver'), function ($query) use ($request) {
                return $query->where('caregiver_id', $request->caregiver);
            })
            ->when($request->filled('client'), function ($query) use ($request) {
                return $query->where('client_id', $request->client);
            })
            ->when($request->filled('prospect'), function ($query) use ($request) {
                return $query->where('prospect_id', $request->prospect);
            })
            ->when($request->filled('referral_source'), function ($query) use ($request) {
                return $query->where('referral_source_id', $request->referral_source);
            })
            ->when($request->filled('user'), function ($query) use ($request) {
                return $query->where('created_by', $request->user);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->filled('free_form'), function ($query) use ($request) {
                return $query->where('body', 'like', '%' . $request->free_form . '%');
            })
            ->get();

        if($request->print){
            return $this->printReport($notes);
        }

        return response()->json($notes);
    }

    /**
     * Get the PDF printed output of the report.
     *
     * @return \Illuminate\Http\Response
     */
    public function printReport($data) : \Illuminate\Http\Response
    {
        $html = response(view('business.reports.communication_notes',['data'=>$data]))->getContent();
        $snappy = \App::make('snappy.pdf');

        return Response(
            $snappy->getOutputFromHtml($html),
            200,

            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="Notes.pdf"'
            )
        );
    }

}
