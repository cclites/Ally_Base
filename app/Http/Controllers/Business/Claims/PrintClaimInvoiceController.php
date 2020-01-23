<?php

namespace App\Http\Controllers\Business\Claims;

use App\Http\Controllers\Business\BaseController;
use App\Claims\ClaimInvoiceType;
use App\Claims\ClaimInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use mikehaertl\pdftk\Pdf;

class PrintClaimInvoiceController extends BaseController
{
    /**
     * Print a standard Ally formatted claim invoice.
     *
     * @param ClaimInvoice $claim
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function standard(ClaimInvoice $claim, Request $request)
    {
        $this->authorize('read', $claim);

        $client = null;
        if ($claim->getType() == ClaimInvoiceType::PAYER()) {

            $groups = $claim->items->sortBy(function ($item) {
                return local_date($item->date, 'm/d/Y', auth()->user()->getTimezone())
                    . ' ' . $item->claimable->getName() . ' ' . $item->getCaregiverName();
            })
                ->mapToGroups(function ($item) {
                    return [$item->getClientName() => $item];
                })
                ->map(function ($group) {
                    return [
                        'client' => $group->first()->getClientName(),
                        'items' => $group,
                        'units' => $group->bcsum('units'),
                        'amount' => $group->bcsum('amount'),
                        'amount_due' => $group->bcsum('amount_due'),
                    ];
                })
                ->sortBy(function ($value, $key) {
                    return $key;
                });;

        } else {
            $client = $claim->client ? $claim->client : $service->client;

            $groups = $claim->items->groupBy('type');
            if (!isset($groups['Expense'])) {
                $groups['Expense'] = [];
            }
            if (!isset($groups['Service'])) {
                $groups['Service'] = [];
            }
        }

        $layout = 'claims.standard';
        if ($claim->getType() == \App\Claims\ClaimInvoiceType::PAYER()) {
            $layout = 'claims.standard-multi-client';
        } else if ($claim->payer_id == \App\Billing\Payer::PRIVATE_PAY_ID) {
            $layout = 'claims.standard-private-pay';
        }

        $view = view($layout, [
            'claim' => $claim,
            'sender' => $claim->business,
            'recipient' => $claim->payer,
            'client' => $client,
            'itemGroups' => $groups,
            'render' => 'html',
            'notes' => $claim->getInvoiceNotesData(),
            'clientData' => $claim->getInvoiceClientData(),
            'override_ally_logo' => $claim->business->logo,
        ]);

        if ($request->filled('download')) {
            $pdfWrapper = app('snappy.pdf.wrapper');
            $pdfWrapper->loadHTML($view->render());
            return $pdfWrapper->download('C-Invoice-' . snake_case($claim->name) . '.pdf');
        }

        return $view;
    }

    /**
     * Print "full" LTCI claims invoice format.
     *
     * @param ClaimInvoice $claim
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function full(ClaimInvoice $claim, Request $request)
    {
        $this->authorize('read', $claim);

        $client = $claim->client ? $claim->client : $service->client;

        $groups = $claim->items->groupBy('type');
        if (!isset($groups['Expense'])) {
            $groups['Expense'] = [];
        }
        if (!isset($groups['Service'])) {
            $groups['Service'] = [];
        }

        $view = view('claims.full', [
            'claim' => $claim,
            'sender' => $claim->business,
            'recipient' => $claim->payer,
            'client' => $client,
            'itemGroups' => $groups,
            'render' => 'html',
            'notes' => $claim->getInvoiceNotesData(),
            'clientData' => $claim->getInvoiceClientData(),
            'override_ally_logo' => $claim->business->logo,
        ]);

        if ($request->filled('download')) {
            $pdfWrapper = app('snappy.pdf.wrapper');
            $pdfWrapper->loadHTML($view->render());
            return $pdfWrapper->download('C-Invoice-' . snake_case($claim->name) . '.pdf');
        }

        return $view;
    }

    /**
     * Print CMS-1500 invoice format.
     *
     * @param ClaimInvoice $claim
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function cmsInvoice(ClaimInvoice $claim, Request $request)
    {
        // dd( $claim, $claim->client->primaryPayer, $claim->client->evvAddress, $claim->client->evvPhone, $claim->client->evvPhone, $claim->serviceItems );
        $this->authorize('read', $claim);

        $claim->load([ 'client', 'client.primaryPayer', 'client.evvPhone', 'serviceItems', 'business' ]);

        $client = $claim->client;
        // $address = $client->something;
        $payer = $client->primaryPayer;
        $serviceItems = $claim->serviceItems;
        $business = $claim->business;

        $pdf = new Pdf( '../resources/pdf_forms/cms1500/editable_form.pdf' );

        $todays_date = Carbon::now()->format( 'm/d/Y' );

        $client_bday_year = Carbon::parse( $client->date_of_birth )->format( 'Y' );
        $client_bday_month = Carbon::parse( $client->date_of_birth )->format( 'm' );
        $client_bday_day = Carbon::parse( $client->date_of_birth )->format( 'd' );
        $client_area_code = substr( $client->evvPhone->national_number, 0, 3 );
        $client_phone = substr( $client->evvPhone->national_number, -7 );

        $total_charge = 0.00;
        $amount_paid = 0.00;
        $diagnosis_incrementer = 1;
        foreach( $serviceItems as $key => $item ){

            $count = $key + 1;

            $total_charge += $item->amount;
            $amount_paid += ( $item->amount_due - $item->amount );

            $pdf = new Pdf( $pdf );
            $pdf->fillForm([

                "service_{$count}_name"           => $item->claimable->service_name,
                "service_{$count}_charge_dollars" => explode( '.', $item->amount )[ 0 ],
                "service_{$count}_charge_change"  => explode( '.', $item->amount )[ 1 ] ?? '00',
                "service_{$count}_units"          => $item->units,
                "service_{$count}_from_month"     => $item->claimable->visit_start_time->format( 'm' ), // $claimable->service->checked_in_time
                "service_{$count}_from_day"       => $item->claimable->visit_start_time->format( 'd' ), // $claimable->service->checked_in_time
                "service_{$count}_from_year"      => $item->claimable->visit_start_time->format( 'Y' ), // $claimable->service->checked_in_time
                "service_{$count}_to_month"       => $item->claimable->visit_end_time->format( 'm' ), // $claimable->service->checked_out_time
                "service_{$count}_to_day"         => $item->claimable->visit_end_time->format( 'd' ), // $claimable->service->checked_out_time
                "service_{$count}_to_year"        => $item->claimable->visit_end_time->format( 'Y' ), // $claimable->service->checked_out_time
                "service_{$count}_place"          => 12, // 24.b Place of Service => **ALWAYS 12**
                "service_{$count}_code"           => $item->claimable->service_code, // 24.d CPT ( Service Code ) with MODs => $claim->CPT
                "service_{$count}_npi"            => $business->medicaid_npi_number, // 24.j Pull NPI based off of client branch location
            ])->needAppearances();

            if( !empty( $item->client_medicaid_diagnosis_codes ) ){

                foreach( explode( ',', $item->client_medicaid_diagnosis_codes ) as $key => $diagnosis ){

                    $pdf = new Pdf( $pdf );
                    $pdf->fillForm([ "diagnosis_code_$diagnosis_incrementer" => trim( $diagnosis )])->needAppearances();
                    $diagnosis_incrementer++;
                }
            }
        }

        $rndm = rand( 10000, 99999 );

        $pdf = new Pdf( $pdf );
        $pdf->fillForm([

            // text-fields **PULL FROM PAYER**
            'insurance_name'           => $payer->payer->name,
            'insurance_address'        => $payer->payer->address1,
            'insurance_address2'       => $payer->payer->address2,
            'insurance_city_state_zip' => $payer->payer->city . ", " . $payer->payer->state . ", " . $payer->payer->zip,

            // Insured Person Information
            'ins_program_name'        => $payer->payer->name, // $client->payer.. the plan or program name
            'ins_insurance_id_number' => $client->hic, // $client->hic
            'ins_name_last_first'     => $client->name_last_first, // $client->name_last_first
            'ins_address'             => $client->evvAddress ? $client->evvAddress->address1 . ' ' . $client->evvAddress->address2 : '', // $client->evvAddress details
            'ins_city'                => $client->evvAddress ? $client->evvAddress->city : '', // $client->evvAddress details
            'ins_state'               => $client->evvAddress ? $client->evvAddress->state : '', // $client->evvAddress details
            'ins_zip_code'            => $client->evvAddress ? $client->evvAddress->zip : '', // $client->evvAddress details
            'ins_phone_area_code'     => $client_area_code, // $client->primary_phone_number.. figure this out and break it up
            'ins_phone_number'        => $client_phone, // $client->primary_phone_number.. figure this out and break it up
            'ins_policy_number'       => $payer->policy_number, // $client->payer Policy Number ( HIC for MCO ).. whatever that means
            'ins_gender'              => strtolower( $client->gender ), // options: [ m, f ] $client->gender
            'ins_bday_month'          => $client_bday_month, // $client->bday.. will have to be broken up
            'ins_bday_day'            => $client_bday_day, // $client->bday.. will have to be broken up
            'ins_bday_year'           => $client_bday_year, // $client->bday.. will have to be broken up
            'ins_signature'           => 'SIGNATURE ON FILE', // SHOULD ALWAYS SAY **SIGNATURE ON FILE**

            // Patient Information
            'pt_ins_relationship' => 'self', // options: [ self, spouse, child, other ] **ALWAYS DO SELF**
            'pt_name_last_first'  => $client->name_last_first, // $client->name_last_first
            'pt_address'          => $client->evvAddress ? $client->evvAddress->address1 : '', // $client->evvAddress details
            'pt_city'             => $client->evvAddress ? $client->evvAddress->city : '', // $client->evvAddress details
            'pt_state'            => $client->evvAddress ? $client->evvAddress->state : '', // $client->evvAddress details
            'pt_zip_code'         => $client->evvAddress ? $client->evvAddress->zip : '', // $client->evvAddress details
            'pt_phone_area_code'  => $client_area_code, // $client->primary_phone_number.. figure this out and break it up
            'pt_phone_number'     => $client_phone, // $client->primary_phone_number.. figure this out and break it up
            'pt_policy_number'    => $payer->policy_number, // $client->payer Policy Number ( HIC for MCO ).. whatever that means
            'pt_gender'           => strtolower( $client->gender ), // options: [ m, f ] $client->gender
            'pt_bday_month'       => $client_bday_month, // $client->bday.. will have to be broken up
            'pt_bday_day'         => $client_bday_day, // $client->bday.. will have to be broken up
            'pt_bday_year'        => $client_bday_year, // $client->bday.. will have to be broken up
            'pt_signature'        => 'SIGNATURE ON FILE', // SHOULD ALWAYS SAY **SIGNATURE ON FILE**
            'pt_todays_date'      => $todays_date,

            'condition_related_to_employment'     => 'n', // options: [ y, n ] **ALWAYS NO**
            'condition_related_to_auto_accident'  => 'n', // options: [ y, n ] **ALWAYS NO**
            'condition_related_to_other_accident' => 'n', // options: [ y, n ] **ALWAYS NO**

            'prior_authorization_number' => 'need to figure out', // TODO => need to figure this one out

            'federal_tax_id'         => $business->medicaid_npi_number, // 25 Federal Tax ID => same as 24.j Pull NPI based off of client branch location
            'patient_account_number' => $claim->getName(), // 26 Patient Account Number => Invoice #
            'total_charge_dollars'   => explode( '.', $total_charge )[ 0 ], // 28
            'total_charge_change'    => explode( '.', $total_charge )[ 1 ] ?? '00', // 28 Total Charges => Sum of all charges on page (not claim total)
            'amount_paid_dollars'    => explode( '.', $amount_paid )[ 0 ], // 29 Amount Paid => Typically 0 or left blank
            'amount_paid_change'     => explode( '.', $amount_paid )[ 1 ] ?? '00', // 29 Amount Paid => Typically 0 or left blank
            'balance_due_dollars'    => explode( '.', $total_charge - $amount_paid )[ 0 ], // 30 Balance Due => Sum of charges minus payments (on page not claim total)
            'balance_due_change'     => explode( '.', $total_charge - $amount_paid )[ 1 ] ?? '00', // 30 Balance Due => Sum of charges minus payments (on page not claim total)

            // NEED TO CLARIFY
            'service_facility_line_1'       => $business->address1 . ' ' . $business->address2, // 32 Service Facility => Location Pull branch specific location info based off branch designation **THESE ARE THE SAME**
            'service_facility_line_2'       => $business->city . ', ' . $business->state . ', ' . $business->zip, // 32 Service Facility => Location Pull branch specific location info based off branch designation **THESE ARE THE SAME**
            'billing_provider_line_1'       => $business->address1 . ' ' . $business->address2, // 33 Billing Provider => Info Pull branch specific location info based off branch designation **THESE ARE THE SAME**
            'billing_provider_line_2'       => $business->city . ', ' . $business->state . ', ' . $business->zip, // 33 Billing Provider => Info Pull branch specific location info based off branch designation **THESE ARE THE SAME**
        ])
            ->needAppearances()
            ->execute();
            // ->saveAs( "../resources/pdf_forms/cms1500/testingOutput$rndm.pdf" );

        $pdf->send( "testingOutput$rndm.pdf" );
        dd( 'remaining here', $pdf );
    }
}