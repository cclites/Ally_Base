<?php

namespace App\Http\Controllers;

use App\Knowledge;
use App\Attachment;
use Illuminate\Support\Facades\DB;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

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
                    ->withKeyword(request()->q)
                    ->ordered()
                    ->get();

                return response()->json($knowledge);
            } else {
                return response()->json([]);
            }
        }

        $knowledge = Knowledge::forRoles($roles)->ordered()->get();

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

    /**
     * View the Tellus Guide
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function tellusGuide()
    {
        // This gets all text code values from the dictionary for the queried categories
        // and only shows the ones that are accepted in the Tellus XSD validation file.
        $xsd = DB::table( 'tellus_typecodes as dictionary' )
            ->whereIn( 'dictionary.category', [ 'Jurisdiction', 'Payer', 'Plan', 'Program' ])
            ->leftJoin( 'tellus_enumerations as xsd', function( $join ){
                $join->on( 'xsd.code', '=', 'dictionary.code' );
                $join->on( 'xsd.category', '=', 'dictionary.category' );
            })
            ->select( 'xsd.id', 'dictionary.description', 'dictionary.category', 'dictionary.code', 'dictionary.text_code' )
            ->whereNotNull( 'xsd.id' )
            ->get();

        if (empty($xsd)) {
            return ErrorResponse(404, 'Error Generating Tellus Documentation');
        }

        $pdf = PDF::loadView( 'tellus-guide', [ 'categories' => $xsd->groupBy( 'category' ) ] );
        return $pdf->stream( 'tellus-guides.pdf' );
    }
}
