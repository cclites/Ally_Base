<?php

namespace App\Http\Controllers\Business;

use App\Billing\ClientInvoice;
use App\Claims\ClaimInvoiceFactory;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClaimController extends Controller
{
    /**
     * Create a ClaimInvoice.
     *
     * @param Request $request
     * @param ClaimInvoiceFactory $factory
     * @return SuccessResponse
     * @throws \Exception
     */
    public function store(Request $request, ClaimInvoiceFactory $factory)
    {
        $clientInvoice = ClientInvoice::findOrFail($request->client_invoice_id);

        $claim = $factory->createFromClientInvoice($clientInvoice);

        return new SuccessResponse('Claim has been created.', compact('claim'));
    }
}
