<?php

namespace App\Http\Controllers;

use App\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * List documents.
     */
    public function index(Request $request)
    {
        $documents = $request->user()->documents;
        return $documents;
    }

    /**
     * Upload and save new document.
     */
    public function store(Request $request)
    {
        $file = $request->file('document');
        $file->store('documents');
        $request->user()->documents()->create([
            'name' => $request->input('name'),
            'filename' => $file->hashName(),
            'original_filename' => $file->getClientOriginalName(),
            'type' => $request->input('type'),
        ]);
        $request->session()->flash('status', 'Document uploaded.');
        return redirect('/documents');
    }
}
