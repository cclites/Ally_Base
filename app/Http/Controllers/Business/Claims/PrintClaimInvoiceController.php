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
        dd( $claim, $claim->client->primaryPayer, $claim->serviceItems );
        $this->authorize('read', $claim);

        $claim->load([ 'client', 'client.primaryPayer', 'serviceItems', 'business' ]);

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

        $thing = $pdf->fillForm([

            // text-fields
            'insurance_name'           => 'Go Fuck Yourself Insurance',
            'insurance_address'        => '9 Burning Tree Lane',
            'insurance_address2'       => 'Unit 107',
            'insurance_city_state_zip' => 'Boca raton, Fl, 33431',

            // Insured Person Information
            'ins_program_name'        => $payer->payer->name, // $client->payer.. the plan or program name
            'ins_insurance_id_number' => $client->hic, // $client->hic
            'ins_name_last_first'     => $client->name_last_first, // $client->name_last_first
            'ins_address'             => '5188 somethign something road', // $client->serviceAddress details
            'ins_city'                => 'fort lauderdale', // $client->serviceAddress details
            'ins_state'               => 'fl', // $client->serviceAddress details
            'ins_zip_code'            => '33445', // $client->serviceAddress details
            'ins_phone_area_code'     => '561', // $client->primary_phone_number.. figure this out and break it up
            'ins_phone_number'        => '6999715', // $client->primary_phone_number.. figure this out and break it up
            'ins_policy_number'       => '1337ssdpol13', // $client->payer Policy Number ( HIC for MCO ).. whatever that means
            'ins_gender'              => strtolower( $client->gender ), // options: [ m, f ] $client->gender
            'ins_bday_month'          => $client_bday_month, // $client->bday.. will have to be broken up
            'ins_bday_day'            => $client_bday_day, // $client->bday.. will have to be broken up
            'ins_bday_year'           => $client_bday_year, // $client->bday.. will have to be broken up
            'ins_signature'           => 'SIGNATURE ON FILE', // SHOULD ALWAYS SAY **SIGNATURE ON FILE**

            'pt_ins_relationship' => 'self', // options: [ self, spouse, child, other ] **ALWAYS DO SELF**

            // Patient Information
            'pt_name_last_first' => $client->name_last_first, // $client->name_last_first
            'pt_address'         => '5188 somethign something road', // $client->serviceAddress details
            'pt_city'            => 'fort lauderdale', // $client->serviceAddress details
            'pt_state'           => 'fl', // $client->serviceAddress details
            'pt_zip_code'        => '33445', // $client->serviceAddress details
            'pt_phone_area_code' => '561', // $client->primary_phone_number.. figure this out and break it up
            'pt_phone_number'    => '6999715', // $client->primary_phone_number.. figure this out and break it up
            'pt_policy_number'   => '1337ssdpol13', // $client->payer Policy Number ( HIC for MCO ).. whatever that means
            'pt_gender'          => strtolower( $client->gender ), // options: [ m, f ] $client->gender
            'pt_bday_month'      => $client_bday_month, // $client->bday.. will have to be broken up
            'pt_bday_day'        => $client_bday_day, // $client->bday.. will have to be broken up
            'pt_bday_year'       => $client_bday_year, // $client->bday.. will have to be broken up
            'pt_signature'       => 'SIGNATURE ON FILE', // SHOULD ALWAYS SAY **SIGNATURE ON FILE**
            'pt_todays_date'     => $todays_date,

            'condition_related_to_employment'     => 'n', // options: [ y, n ] **ALWAYS NO**
            'condition_related_to_auto_accident'  => 'n', // options: [ y, n ] **ALWAYS NO**
            'condition_related_to_other_accident' => 'n', // options: [ y, n ] **ALWAYS NO**

            // Check Insurance and Service Auths.. Medical Diagnosis Codes
            'diagnosis_code_1' => '0123',
            'diagnosis_code_2' => '0123',
            'diagnosis_code_3' => '0123',
            'diagnosis_code_4' => '0123',
            'diagnosis_code_5' => '0123',
            'diagnosis_code_6' => '0123',
            'diagnosis_code_7' => '0123',
            'diagnosis_code_8' => '0123',
            'diagnosis_code_9' => '0123',
            'diagnosis_code_10' => '0123',
            'diagnosis_code_11' => '0123',
            'diagnosis_code_12' => '0123',

            'prior_authorization_number' => 'asd123ddsas1zaqwsx', // TODO => need to figure this one out

            'service_1_name'           => 'Testicular Care',
            'service_1_charge_dollars' => 456,
            'service_1_charge_change'  => 54,
            'service_1_units'          => '45',
            'service_1_from_month'     => '02', // $claimable->service->checked_in_time
            'service_1_from_day'       => '12', // $claimable->service->checked_in_time
            'service_1_from_year'      => '1994', // $claimable->service->checked_in_time
            'service_1_to_month'       => '04', // $claimable->service->checked_out_time
            'service_1_to_day'         => '20', // $claimable->service->checked_out_time
            'service_1_to_year'        => '1994', // $claimable->service->checked_out_time
            'service_1_place'          => 12, // 24.b Place of Service => **ALWAYS 12**
            'service_1_code'           => '', // 24.d CPT ( Service Code ) with MODs => $claim->CPT
            'service_1_npi'            => $business->medicaid_npi_number, // 24.j Pull NPI based off of client branch location

            'federal_tax_id'         => $business->medicaid_npi_number, // 25 Federal Tax ID => same as 24.j Pull NPI based off of client branch location
            'patient_account_number' => '', // 26 Patient Account Number => Pull based off of Ally assigned ID
            'total_charge_dollars'   => 123, // 28
            'total_charge_change'    => 20, // 28 Total Charges => Sum of all charges on page (not claim total)
            'amount_paid_dollars'    => 100, // 29 Amount Paid => Typically 0 or left blank
            'amount_paid_change'     => 20, // 29 Amount Paid => Typically 0 or left blank
            'blanace_due'            => 123.20 - 100.20, // 30 Balance Due => Sum of charges minus payments (on page not claim total)

            // NEED TO CLARIFY
            'supplier_signature'     => '', // 31 Signature of Supplier => Pull from Contacts within Payer Setup 
            'service_provider'       => '', // 32 Service Facility => Location Pull branch specific location info based off branch designation
            'billing_provider'       => '', // 33 Billing Provider => Info Pull branch specific location info based off branch designation
            
            'todays_date'            => $todays_date
        ])
            ->flatten()
            ->saveAs( '../resources/pdf_forms/cms1500/jeeee.pdf' );

        // $thing = $pdf->dropXfa()->flatten()->saveAs( '../resources/pdf_forms/cms1500/fuck.pdf' );

        dd( $thing, $pdf );
    }
}