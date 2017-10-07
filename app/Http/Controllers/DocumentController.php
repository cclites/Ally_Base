<?php

namespace App\Http\Controllers;

use App\User;
use App\Document;
use Illuminate\Http\Request;
use App\Responses\SuccessResponse;

class DocumentController extends Controller
{
    /**
     * List documents.
     * TODO: check permissions
     */
    public function index(User $user)
    {
        return $user->documents;
    }

    /**
     * Upload and save new document.
     * TODO: add validation and error checking
     */
    public function store(Request $request)
    {
        $file = $request->file('file');
        $file->store('documents');
        User::find($request->input('user_id'))->documents()->create([
            'name' => $request->input('name'),
            'filename' => $file->hashName(),
            'original_filename' => $file->getClientOriginalName(),
            'type' => $request->input('type'),
        ]);
        return new SuccessResponse('Document uploaded.');
    }

    /**
     * Download document.
     * TODO: check permissions
     */
    public function download(Document $document)
    {
        $path = storage_path('app/documents/' . $document->filename);
        return response()->download($path, $document->original_filename);
    }
}
