<?php

namespace App\Http\Controllers;

use App\Note;
use App\OfficeUser;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        $this->validate($request, [
            'body' => 'required|string'
        ]);

        $note = Note::create([
            'business_id' => $request->business_id,
            'caregiver_id' => $request->caregiver_id,
            'client_id' => $request->client_id,
            'tags' => $request->tags,
            'body' => $request->body,
            'created_by' => auth()->id()
        ]);
        if ($note) {
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
     * @param  \App\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function edit(Note $note)
    {
        //
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
        //
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
}
