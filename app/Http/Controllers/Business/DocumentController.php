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
     */
    public function index(User $user)
    {
        $this->authorize('read', $user);

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
        $user = User::findOrFail($request->input('user_id'));
        $this->authorize('read', $user);

        $file = $request->file('file');
        $file->store('documents');

        $user->documents()->create([
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
        $this->authorize('read', $document->user);

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
     * @param \App\Document $document
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Document $document)
    {
        $this->authorize('read', $document->user);

        $path = storage_path('app/documents/' . $document->filename);
        return response()->download($path, $document->original_filename);
    }
}
