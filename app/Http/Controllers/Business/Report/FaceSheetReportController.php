<?php


namespace App\Http\Controllers\Business\Report;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Responses\ErrorResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class FaceSheetReportController
{
    public function index(Request $request){
        return view_component('face-sheet', 'Face Sheet', ['role'=>$request->role], [
            'Home' => route('home'),
            'Reports' => route('business.reports.face-sheet')
        ]);
    }

    public function generateClientFaceSheet(Client $client){

        if(!$client){
            return new ErrorResponse("404", 'You must select a client.');
        }

        $client = $client->load(['addresses', 'preferences', 'business', 'careDetails', 'contacts']);
        $html = response(view('business.clients.client_face_sheet', ['client'=>$client]))->getContent();
        $snappy = \App::make('snappy.pdf');

        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $client->nameLastFirst() . '_face_sheet.pdf"'
            )
        );
    }

    public function generateCaregiverFaceSheet(Caregiver $caregiver, Business $business){

        if(!$caregiver){
            return new ErrorResponse("404", 'You must select a caregiver.');
        }

        $caregiver = $caregiver->load(['addresses', 'businessChains', 'availability', 'skills']);
        $html = response(view('business.caregivers.caregiver_face_sheet', ['caregiver'=>$caregiver, 'business'=>$business]))->getContent();
        $snappy = \App::make('snappy.pdf');

        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $caregiver->nameLastFirst() . '_face_sheet.pdf"'
            )
        );

    }
}