<?php

namespace App\Http\Controllers\Admin;

use App\BusinessChain;
use App\Http\Controllers\Controller;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class AveryController extends Controller
{
    /**
     * Get listing of all
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $chains = BusinessChain::ordered()->get();

        return view( 'admin.avery.index' )->with( compact( 'chains' ) );
    }

    /**
     * 
     * print out the avery labels
     */
    public function printLabels()
    {

        // $xsd = DB::table( 'tellus_typecodes as dictionary' )
        // ->whereIn( 'dictionary.category', [ 'Payer', 'Plan' ])
        // ->leftJoin( 'tellus_enumerations as xsd', function( $join ){

        //     $join->on( 'xsd.code', '=', 'dictionary.code' );
        //     $join->on( 'xsd.category', '=', 'dictionary.category' );
        // })
        // ->select( 'xsd.id', 'dictionary.description', 'dictionary.category', 'dictionary.code', 'dictionary.text_code' )
        // ->whereNotNull( 'xsd.id' )
        // ->get();

        // if (empty($xsd)) {
        //     return ErrorResponse(404, 'Error Generating Tellus Documentation');
        // }

        // $pdf = PDF::loadView( 'tellus-guide', [ 'categories' => $xsd->groupBy( 'category' ) ] );

        // return $pdf->stream( 'tellus-guides.pdf' );
    }
}
