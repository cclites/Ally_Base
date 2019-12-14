<?php

namespace App\Http\Controllers\Business\Claims;

use App\Http\Controllers\Business\BaseController;
use App\Claims\ClaimInvoiceType;
use App\Claims\ClaimInvoice;
use Illuminate\Http\Request;

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
            return $pdfWrapper->download('Claim-Invoice-' . snake_case($claim->name) . '.pdf');
        }

        return $view;
    }

}