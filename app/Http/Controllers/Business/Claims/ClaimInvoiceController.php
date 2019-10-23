<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Exceptions\CannotDeleteClaimInvoiceException;
use App\Claims\Requests\GetClaimInvoicesRequest;
use App\Claims\Resources\ClaimsQueueResource;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Requests\UpdateClaimInvoiceRequest;
use App\Claims\Resources\ClaimInvoiceResource;
use App\Claims\Factories\ClaimInvoiceFactory;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\Billing\ClientInvoice;
use App\Billing\ClaimStatus;
use App\Claims\ClaimInvoice;
use Illuminate\Http\Request;

class ClaimInvoiceController extends BaseController
{
    /**
     * Get a list of Claim Invoices.
     *
     * @param GetClaimInvoicesRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(GetClaimInvoicesRequest $request)
    {
        $filters = $request->filtered();

        $query = ClaimInvoice::with(['items' => function ($q) {
            $q->orderByRaw('claimable_type desc, date asc');
        }])->forRequestedBusinesses()
            ->forPayer($filters['payer_id'])
            ->forClient($filters['client_id'])
            ->forClientType($filters['client_type'])
            ->searchForInvoiceId($filters['invoice_id'])
            ->whereIn('status', ClaimStatus::transmittedStatuses());

        if ($request->getDateSearchType() == 'invoice') {
            $query->whereInvoicedBetween($filters['start_date'], $filters['end_date']);
        } else {
            $query->whereDatesOfServiceBetween($filters['start_date'], $filters['end_date']);
        }

        if (! $filters['inactive']) {
            $query->forActiveClientsOnly();
        }

        if ($request->claim_status == 'unpaid') {
            $query = $query->hasBalance();
        }

        $results = $query->get();

        return ClaimInvoiceResource::collection($results);
    }

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

        $this->authorize('read', $clientInvoice);

        list($claim, $warnings) = $factory->createFromClientInvoice($clientInvoice);

        $message = 'Claim has been created.';
        if ($warnings->count() > 0) {
            $message = "Claim was created but produced the following warnings:\r\n";
            foreach ($warnings as $item) {
                $message .= "$item\r\n";
            }
        }
        return new SuccessResponse($message, new ClaimsQueueResource($claim->clientInvoice->fresh()));
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

        return view_component(
            'claim-editor',
            'Edit Claim #' . $claim->name,
            ['original-claim' => new ClaimInvoiceResource($claim)],
            ['Home' => '/', 'Claims Queue' => route('business.claims-queue')]
        );
    }

    /**
     * Update the ClaimInvoice.
     *
     * @param ClaimInvoice $claim
     * @param UpdateClaimInvoiceRequest $request
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(ClaimInvoice $claim, UpdateClaimInvoiceRequest $request)
    {
        $this->authorize('update', $claim);

        if ($claim->update($request->filtered())) {
            $claim->markAsModified();
            return new SuccessResponse('Claim information has been saved.', new ClaimInvoiceResource($claim));
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to save this claim.  Please try again.');
    }

    /**
     * View a Claim Invoice.
     *
     * @param ClaimInvoice $claim
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(ClaimInvoice $claim)
    {
        $this->authorize('read', $claim);

        return response()->json(new ClaimInvoiceResource($claim));
    }

    /**
     * Delete a ClaimInvoice.
     *
     * @param ClaimInvoice $claim
     * @param ClaimInvoiceFactory $factory
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(ClaimInvoice $claim, ClaimInvoiceFactory $factory)
    {
        $this->authorize('delete', $claim);

        if ($claim->adjustments()->count() > 0) {
            return new ErrorResponse(500, 'Cannot delete Claims that have adjustments applied.');
        }

        if ($claim->hasBeenTransmitted()) {
            return new ErrorResponse(500, 'Cannot delete Claims that have been transmitted.');
        }

        $clientInvoice = $claim->clientInvoice;

        try {
            $factory->deleteClaimInvoice($claim);
            return new SuccessResponse('Claim has been deleted.', new ClaimsQueueResource($clientInvoice->fresh()));
        } catch (CannotDeleteClaimInvoiceException $ex) {
            return new ErrorResponse(500, 'Could not delete this Claim: ' . $ex->getMessage());
        }
    }

    /**
     * Print a ClaimInvoice.
     *
     * @param ClaimInvoice $claim
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function print(ClaimInvoice $claim, Request $request)
    {
        $this->authorize('read', $claim);

        $groups = $claim->items->groupBy('type');

        if (!isset($groups['Expense'])) {
            $groups['Expense'] = [];
        }
        if (!isset($groups['Service'])) {
            $groups['Service'] = [];
        }

        $view = view('claims.claim_invoice', [
            'claim' => $claim,
            'sender' => $claim->business,
            'recipient' => $claim->payer,
            'client' => $claim->client,
            'itemGroups' => $groups,
            'clientPayer' => $claim->getClientPayer(),
            'render' => 'html',
        ]);

        if ($request->filled('download')) {
            $pdfWrapper = app('snappy.pdf.wrapper');
            $pdfWrapper->loadHTML($view->render());
            return $pdfWrapper->download('Claim-Invoice-' . snake_case($claim->name) . '.pdf');
        }

        return $view;
    }
}
