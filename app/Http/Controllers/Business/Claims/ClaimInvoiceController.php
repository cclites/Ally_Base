<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Exceptions\CannotDeleteClaimInvoiceException;
use App\Claims\Requests\UpdateClaimInvoiceRequest;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Requests\GetClaimInvoicesRequest;
use App\Claims\Resources\ClaimInvoiceResource;
use App\Claims\Resources\ClaimCreatorResource;
use App\Claims\Factories\ClaimInvoiceFactory;
use App\Claims\Queries\ClaimInvoiceQuery;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\Billing\ClientInvoice;
use App\Claims\ClaimStatus;
use App\Claims\ClaimInvoice;
use Illuminate\Http\Request;

class ClaimInvoiceController extends BaseController
{
    /**
     * Get a list of Claim Invoices.
     *
     * @param GetClaimInvoicesRequest $request
     * @param ClaimInvoiceQuery $claimQuery
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(GetClaimInvoicesRequest $request, ClaimInvoiceQuery $claimQuery)
    {
        $filters = $request->filtered();

        $claimQuery->with([
            'clientInvoices.client',
            'items' => function ($q) {
                $q->orderByRaw('claimable_type desc, date asc');
            },
            'items.clientInvoice',
        ])->forRequestedBusinesses()
            ->withStatus(ClaimStatus::transmittedStatuses())
            ->when($filters['client_id'], function (ClaimInvoiceQuery $q, $var) {
                $q->forClient($var);
            })
            ->when(filled($filters['payer_id']), function (ClaimInvoiceQuery $q) use ($filters) {
                $q->forPayer($filters['payer_id']);
            })
            ->when($filters['client_type'], function (ClaimInvoiceQuery $q, $var) {
                $q->forClientType($var);
            })
            ->when($filters['invoice_id'], function (ClaimInvoiceQuery $q, $var) {
                $q->searchForInvoiceId($var);
            })
            ->when(!$filters['inactive'], function (ClaimInvoiceQuery $q) {
                $q->forActiveClientsOnly();
            })
            ->when($filters['claim_status'] == 'unpaid', function (ClaimInvoiceQuery $q) {
                $q->notPaidInFull();
            })
            ->when($filters['claim_type'], function (ClaimInvoiceQuery $q, $var) {
                $q->withType($var);
            });

        if ($request->getDateSearchType() == 'invoice') {
            $claimQuery->whereInvoicedBetween($request->filterDateRange());
        } else {
            $claimQuery->whereDatesOfServiceBetween($request->filterDateRange());
        }

        return ClaimInvoiceResource::collection($claimQuery->get());
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

        try {
            list($claim, $warnings) = $factory->createFromClientInvoice($clientInvoice);
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
        return new SuccessResponse($message, new ClaimCreatorResource($clientInvoice->fresh()));
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
            ['Home' => '/', 'Manage Claims' => route('business.claims-manager')]
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
     * @param Request $request
     * @param ClaimInvoice $claim
     * @param ClaimInvoiceFactory $factory
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Request $request, ClaimInvoice $claim, ClaimInvoiceFactory $factory)
    {
        $this->authorize('delete', $claim);

        if (! $request->force && $claim->adjustments()->count() > 0) {
            return new ErrorResponse(412, 'This claim has had adjustments applied.');
        }

        try {
            $factory->deleteClaimInvoice($claim);
            return new SuccessResponse('Claim has been deleted.');
        } catch (CannotDeleteClaimInvoiceException $ex) {
            return new ErrorResponse(500, 'Could not delete this Claim: ' . $ex->getMessage());
        }
    }

}
