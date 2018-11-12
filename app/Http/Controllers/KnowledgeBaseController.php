<?php

namespace App\Http\Controllers;

use App\Knowledge;
use App\Attachment;

class KnowledgeBaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = auth()->user()->role_type;
        if (auth()->user()->role_type == 'admin') {
            $roles = ['caregiver', 'client', 'office_user'];
        }

        if (request()->wantsJson()) {
            if (request()->has('q')) {
                $knowledge = Knowledge::forRoles($roles)
                    ->withKeyword(request()->q)->get();

                return response()->json($knowledge);
            } else {
                return response()->json([]);
            }
        }

        $knowledge = Knowledge::forRoles($roles)->get();

        return view('knowledge-base')->with(compact(['knowledge']));
    }

    /**
     * Download an attachment file.
     *
     * @param $attachment
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function attachment($attachment)
    {
        $attachment = Attachment::where('filename', $attachment)->first();

        if (empty($attachment)) {
            return ErrorResponse(404, 'File not found.');
        }

        $path = storage_path('app/knowledge/' . $attachment->filename);

        return response()->download($path, $attachment->filename);
    }
}
