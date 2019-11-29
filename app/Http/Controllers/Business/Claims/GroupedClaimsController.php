<?php

namespace App\Http\Controllers\Business\Claims;

use App\Http\Controllers\Business\BaseController;
use App\Claims\Resources\ClaimsQueueResource;
use App\Claims\Factories\ClaimInvoiceFactory;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class GroupedClaimsController extends BaseController
{
    /**
     * Create a ClaimInvoice.
     *
     * @param Request $request
     * @param ClientInvoiceQuery $invoiceQuery
     * @param ClaimInvoiceFactory $factory
     * @return SuccessResponse
     */
    public function store(Request $request, ClientInvoiceQuery $invoiceQuery, ClaimInvoiceFactory $factory)
    {
        $invoices = $invoiceQuery->whereIn('id', $request->selected_ids)
            ->forRequestedBusinesses() // No businesses param in the request so this should filter all authorized
            ->get();

        list($claim, $warnings) = $factory->createFromClientInvoices($invoices);

        $message = 'Claim has been created.';
        if ($warnings->count() > 0) {
            $message = "Claim was created but produced the following warnings:\r\n";
            foreach ($warnings as $item) {
                $message .= "$item\r\n";
            }
        }
        return new SuccessResponse($message, new ClaimsQueueResource($claim->clientInvoice->fresh()));
    }
}