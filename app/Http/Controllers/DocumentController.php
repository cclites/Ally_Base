<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * List documents.
     */
    public function index()
    {
        return view('documents.index');
    }
}
