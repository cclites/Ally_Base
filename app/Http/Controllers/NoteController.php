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
        $business = OfficeUser::find(auth()->id())->businesses()->with('caregivers', 'clients', 'notes.caregiver', 'notes.client')->first();
        $business->notes = $business->notes->map(function ($note) {
            $note->body = str_limit($note->body, 70);
            return $note;
        });
        return view('notes.index', compact('business'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $business = OfficeUser::find(auth()->id())
            ->businesses()
            ->with('caregivers', 'clients')
            ->first();
        return view('notes.create', compact('business'));
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
     * Display the specified resource.
     *
     * @param  \App\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function show(Note $note)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $business = OfficeUser::find(auth()->id())->businesses()->with('caregivers', 'clients')->first();
        $note = Note::with('caregiver', 'client', 'creator')->find($id);
        return view('notes.edit', compact('note', 'business'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Note $note)
    {
        $this->validate($request, ['body' => 'required|string']);

        $result = $note->update($request->only(['body', 'tags', 'client_id', 'caregiver_id']));

        if ($result) {
            return new SuccessResponse('Note updated.', [], '/notes');
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
        //
    }

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
