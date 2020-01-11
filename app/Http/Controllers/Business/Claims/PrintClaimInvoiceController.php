<?php

namespace App\Http\Controllers\Business\Claims;

use App\Http\Controllers\Business\BaseController;
use App\Claims\ClaimInvoiceType;
use App\Claims\ClaimInvoice;
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
        $this->authorize('read', $claim);

        $pdf = new Pdf( '../resources/pdf_forms/cms1500/editable_form.pdf' );

        $thing = $pdf->fillForm([

            // check boxes
            'insurance_type_medicare'  => 'Yes',
            'insurance_type_medicaide' => null,
            'insurance_type_tricare'   => 'Yes',
            'insurance_type_champva'   => 'Yes',
            'insurance_type_group'     => null,
            'insurance_type_feca'      => 'Yes',
            'insurance_type_other'     => 'Yes',

            // text-fields
            'insurance_name'           => 'Go Fuck Yourself Insurance',
            'insurance_address'        => '9 Burning Tree Lane',
            'insurance_address2'       => 'Unit 107',
            'insurance_city_state_zip' => 'Boca raton, Fl, 33431',

            // Insured Person Information
            'ins_program_name'        => 'This That Insurance',
            'ins_insurance_id_number' => '12apoplejuice',
            'ins_name_last_first'     => 'White, Erik',
            'ins_address'             => '5188 somethign something road',
            'ins_city'                => 'fort lauderdale',
            'ins_state'               => 'fl',
            'ins_zip_code'            => '33445',
            'ins_phone_area_code'     => '561',
            'ins_phone_number'        => '6999715',
            'ins_policy_number'       => '1337ssdpol13',
            'ins_gender_male'         => 'm', // check-box, [ m, f ]
            'ins_bday_month'          => '05',
            'ins_bday_day'            => '20',
            'ins_bday_year'           => '1994',

            'another_benefit_plan' => 'y', // check-box, [ y, n ]
            // below section is only applicable if "another_benefit_plan" is "y"
            'ins_2_program_name'        => 'Aetna or someshit',
            'ins_2_name_last_first'     => 'White, Erik',
            'ins_2_insurance_id_number' => '5188 somethign something road',

            'pt_ins_relationship' => 'child', // check-box, [ self, spouse, child, other ]

            // Patient Information
            'pt_name_last_first' => 'White, Erik',
            'pt_address'         => '5188 somethign something road',
            'pt_city'            => 'fort lauderdale',
            'pt_state'           => 'fl',
            'pt_zip_code'        => '33445',
            'pt_phone_area_code' => '561',
            'pt_phone_number'    => '6999715',
            'pt_policy_number'   => '1337ssdpol13',
            'pt_gender'          => 'm', // check-box, [ m, f ]
            'pt_bday_month'      => '05',
            'pt_bday_day'        => '20',
            'pt_bday_year'       => '1994',

            'condition_related_to_employment'          => 'n',
            'condition_related_to_auto_accident'       => 'y',
            'condition_related_to_auto_accident_state' => 'FL',
            'condition_related_to_other_accident'      => 'y',

            'curr_ill_month' => '05',
            'curr_ill_day'   => '20',
            'curr_ill_year'  => '1994',

            'ref_physician_name'   => 'Thomas Aquinas',
            'ref_physician_npi'    => 'Thomas Aquinas',
            'ref_physician_number' => 'Thomas Aquinas',

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

            'date_unable_to_work_from_month' => '02',
            'date_unable_to_work_from_day'   => '12',
            'date_unable_to_work_from_year'  => '1994',
            'date_unable_to_work_to_month'   => '04',
            'date_unable_to_work_to_day'     => '20',
            'date_unable_to_work_to_year'    => '1994',

            'date_hospitalized_from_month' => '02',
            'date_hospitalized_from_day'   => '12',
            'date_hospitalized_from_year'  => '1994',
            'date_hospitalized_to_month'   => '04',
            'date_hospitalized_to_day'     => '20',
            'date_hospitalized_to_year'    => '1994',

            'outside_lab' => 'y', // check-box, [ y, n ]
            'outside_lab_charges' => '1337.37',
            'prior_authorization_number' => 'asd123ddsas1zaqwsx',

            'service_1_name'       => 'Testicular Care',
            'service_1_charge'     => '456.54',
            'service_1_units'      => '45',
            'service_1_from_month' => '02',
            'service_1_from_day'   => '12',
            'service_1_from_year'  => '1994',
            'service_1_to_month'   => '04',
            'service_1_to_day'     => '20',
            'service_1_to_year'    => '1994',
        ])
            ->flatten()
            ->saveAs( '../resources/pdf_forms/cms1500/jeeee.pdf' );

        // $thing = $pdf->dropXfa()->flatten()->saveAs( '../resources/pdf_forms/cms1500/fuck.pdf' );

        dd( $thing, $pdf );
    }
}