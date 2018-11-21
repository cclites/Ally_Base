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

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notes = Note::forRequestedBusinesses()->ordered()->get();
        $notes = $notes->map(function ($note) {
            $note->body = str_limit($note->body, 70);
            return $note;
        });
        return view('notes.index', compact('notes'));
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

            if ($request->has('modal')) {
                return new CreatedResponse('Note Created', $note->load('creator', 'client', 'caregiver'));
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
            if ($request->has('modal')) {
                return new SuccessResponse('Note has been updated.', $note->fresh()->load('creator', 'client', 'caregiver'));
            }

            return new SuccessResponse('Note has been updated', [], '/notes');
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
            return new SuccessResponse('Note deleted.');
        }

        return new ErrorResponse(500, 'The note could not be deleted.');
    }

    /**
     * Handle search filters.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $notes = Note::with('caregiver', 'client')
            ->where('business_id', OfficeUser::find(auth()->id())->businesses[0]->id)
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
            ->when($request->filled('tags'), function ($query) use ($request) {
                return $query->where('tags', 'like', '%'.$request->tags.'%');
            })
            ->get();

        $notes = $notes->map(function ($note) {
            $note->body = str_limit($note->body, 70);
            return $note;
        });
        return response()->json($notes);
    }
}
