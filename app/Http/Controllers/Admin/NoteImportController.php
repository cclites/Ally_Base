<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class NoteImportController extends Controller
{
    public function view()
    {
        return view( 'admin.import.notes' );
    }
}
