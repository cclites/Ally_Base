<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Resources\ClaimCreatorResource;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Resources\ManageClaimsResource;
use App\Claims\Factories\ClaimInvoiceFactory;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Responses\ErrorResponse;
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
     * @throws \Exception
     */
    public function store(Request $request, ClientInvoiceQuery $invoiceQuery, ClaimInvoiceFactory $factory)
    {
        $invoices = $invoiceQuery->whereIn('id', $request->selected_ids)
            ->forRequestedBusinesses() // No businesses param in the request so this should filter all authorized
            ->get();

        try {
            list($claim, $warnings) = $factory->createFromClientInvoices($invoices);
        } catch (\InvalidArgumentException $ex) {
            return new ErrorResponse(500, 'Error creating claim: ' . $ex->getMessage());
        }

        $message = 'Claim has been created.';
        if ($warnings->count() > 0) {
            $message = "Claim was created but produced the following warnings:\r\n";
            foreach ($warnings as $item) {
                $message .= "$item\r\n";
            }
        }

        return new SuccessResponse($message, ClaimCreatorResource::collection($claim->clientInvoices));
    }
}