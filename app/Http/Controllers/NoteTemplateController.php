<?php

namespace App\Http\Controllers;

use App\NoteTemplate;
use App\OfficeUser;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\CreateNoteTemplateRequest;

class NoteTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $templates = NoteTemplate::forRequestedBusinesses()->ordered()->get();

        if (request()->expectsJson()) {
            return $templates;
        }

        return view('note-templates.index', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateNoteTemplateRequest $request)
    {
        if ($template = NoteTemplate::create(array_merge($request->validated(), [
            'created_by' => auth()->id(),
        ]))) {

            if ($request->input('modal')) {
                return new CreatedResponse('Note template has been created', $template->load('creator'));
            }

            return new CreatedResponse('Note template has been created', [], '/notes');
            
        }
        
        return new ErrorResponse(500, 'The note template could not be created.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\NoteTemplate  $template
     * @return \Illuminate\Http\Response
     */
    public function update(CreateNoteTemplateRequest $request, NoteTemplate $noteTemplate)
    {
        if ($noteTemplate->update($request->validated())) {
            if ($request->input('modal')) {
                return new SuccessResponse('Note template has been updated.', $noteTemplate->fresh()->load('creator'));
            }

            return new SuccessResponse('Note template has been updated');
        }

        return new ErrorResponse(500, 'The note template could not be updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\NoteTemplate  $template
     * @return \Illuminate\Http\Response
     */
    public function destroy(NoteTemplate $noteTemplate)
    {
        if ($noteTemplate->delete()) {
            return new SuccessResponse('Note template has been deleted.');
        }

        return new ErrorResponse(500, 'The note template could not be deleted.');
    }
}
