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
        $templates = $templates->map(function ($template) {
            $template->note = str_limit($template->note, 70);
            return $template;
        });
        return view('note-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('note-templates.create');
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
            return new CreatedResponse('Note template Created', [], '/note-templates');
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
    public function update(CreateNoteTemplateRequest $request, NoteTemplate $template)
    {
        if ($template->update($request->validated())) {
            if ($request->input('modal')) {
                return new SuccessResponse('Note template has been updated.', $template->fresh()->load('creator'));
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
    public function destroy(NoteTemplate $template)
    {
        if ($template->delete()) {
            return new SuccessResponse('Note template has been deleted.');
        }

        return new ErrorResponse(500, 'The note template could not be deleted.'.$template);
    }
}
