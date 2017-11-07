<?php

namespace App\Http\Controllers\Business;

use App\Responses\ErrorResponse;
use App\User;
use App\Document;
use Illuminate\Http\Request;
use App\Responses\SuccessResponse;

class DocumentController extends BaseController
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
     * @param Request $request
     * @return SuccessResponse
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
            'description' => $request->description
        ]);
        return new SuccessResponse('Document uploaded.');
    }

    public function destroy(Document $document)
    {
        try {
            if ($document->delete()) {
                return new SuccessResponse('Document deleted.');
            }
        } catch (\Exception $e) {
            logger($e->getMessage());
        }
        return new ErrorResponse(500, 'Document failed to delete.');
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
