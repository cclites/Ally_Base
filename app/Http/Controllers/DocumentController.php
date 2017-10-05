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
}
