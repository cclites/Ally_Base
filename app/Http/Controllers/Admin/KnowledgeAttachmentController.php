<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Attachment;

class KnowledgeAttachmentController extends Controller
{
    /**
     * Upload a new attachment.
     *
     * @return SuccessResponse
     */
    public function store()
    {
        $file = request()->file('attachment');

        $file->store('knowledge');

        $attachment = Attachment::create([
            'filename' => $file->hashName(),
            'name' => $file->getClientOriginalName(),
        ]);

        return new SuccessResponse("\"{$attachment->filename}\" has been uploaded.", $attachment);
    }

    /**
     * Upload a knowledge video.
     *
     * @return SuccessResponse
     */
    public function storeVideo()
    {
        request()->validate([
            'attachment' => 'mimetypes:video/mp4',
        ], [
            'attachment.*' => 'Only mp4 video files are allowed.'
        ]);

        $file = request()->file('attachment');

        $file->store('knowledge');

        $attachment = Attachment::create([
            'filename' => $file->hashName(),
            'name' => $file->getClientOriginalName(),
        ]);

        return new SuccessResponse("\"{$attachment->filename}\" has been uploaded.", $attachment);
    }
}
