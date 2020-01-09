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

        $pdf = new Pdf( '../resources/pdf_forms/cms1500/cmsform.pdf' );
        $thing = $pdf->fillForm([

            'ref_physician' => 'christ himself',
        ])
            ->needAppearances()
            ->saveAs( 'fuckboi.pdf' );

        dd( $thing, $pdf );
    }
}