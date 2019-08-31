<?php

namespace App\Http\Controllers\Business;

use App\Billing\ClientInvoice;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\Claims\ClaimInvoice;
use App\Claims\ClaimInvoiceFactory;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class ClaimInvoiceController extends BaseController
{
    /**
     * Create a ClaimInvoice.
     *
     * @param Request $request
     * @param ClaimInvoiceFactory $factory
     * @return SuccessResponse
     * @throws \Exception
     */
    public function store( Request $request, ClaimInvoiceFactory $factory )
    {
        $clientInvoice = ClientInvoice::findOrFail( $request->client_invoice_id );

        $this->authorize('read', $clientInvoice);

        $claim = $factory->createFromClientInvoice( $clientInvoice );

        return new SuccessResponse( 'Claim has been created.', compact( 'claim' ) );
    }

    /**
     * grab the data for a specific claim_invoice to populate the edit-modal
     *
     * @param ClaimInvoice $claim
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show( ClaimInvoice $claim )
    {
        $this->authorize('read', $claim);

        $claim->load([ 'items', 'client' ]);

        return response()->json( $claim );
    }

    /**
     * Edit ClaimInvoice form.
     *
     * @param ClaimInvoice $claim
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(ClaimInvoice $claim)
    {
        $this->authorize('read', $claim);

        $claim->load([
            'items',
            'client',
            'business',
            'clientInvoice',
            'payer',
        ]);

        return view_component('claim-details', 'Edit Claim #'.$claim->id, compact('claim'));
    }

    /**
     * Update the ClaimInvoice.
     *
     * @param ClaimInvoice $claim
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update( ClaimInvoice $claim, Request $request )
    {
        $this->authorize('update', $claim);

        // validate data
        $validated = $request->validate([

            'client_first_name'               => 'sometimes|required',
            'client_last_name'                => 'sometimes|required',
            'client_medicaid_diagnosis_codes' => 'nullable',
            'client_medicaid_id'              => 'nullable',
            'payer_code'                      => 'nullable',
            'payer_name'                      => 'sometimes|required',
            'plan_code'                       => 'nullable',
            'transmission_method'             => 'nullable'
        ]);

        // make the update
        $claim->update( $validated );

        // return
        return response()->json( $claim->refresh() );
    }

    /**
     * Create a ClaimInvoice.
     *
     * @param Request $request
     * @param ClaimInvoiceFactory $factory
     * @return SuccessResponse
     * @throws \Exception
     */
    public function destroy( Request $request, ClaimInvoiceFactory $factory )
    {
        $claim = ClaimInvoice::findOrFail( $request->claim );

        $this->authorize('delete', $claim);

        $factory->hardDeleteClaimInvoice( $claim );

        return new SuccessResponse( 'Claim has been deleted.' );
    }

    /**
     *
     * Show a claim_invoice
     *
     * @param ClaimInvoice $claim
     * @param string $view
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function print( ClaimInvoice $claim, string $view = InvoiceViewFactory::HTML_VIEW )
    {
        $this->authorize('read', $claim);

        $strategy = InvoiceViewFactory::create( $claim, $view );

        $viewGenerator = new InvoiceViewGenerator( $strategy );

        return $viewGenerator->generateNewClaimInvoice( $claim );
    }
}
