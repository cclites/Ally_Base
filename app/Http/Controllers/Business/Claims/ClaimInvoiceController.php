<?php

namespace App\Http\Controllers\Business\Claims;

use App\Billing\Contracts\InvoiceInterface;
use App\Claims\ClaimRemit;
use App\Claims\Exceptions\CannotDeleteClaimInvoiceException;
use App\Claims\Requests\GetClaimInvoicesRequest;
use App\Claims\Requests\UpdateClaimInvoiceRequest;
use App\Claims\Resources\ClaimRemitResource;
use App\Contracts\ContactableInterface;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Resources\ClaimInvoiceResource;
use App\Claims\Factories\ClaimInvoiceFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\Billing\View\InvoiceViewFactory;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\Billing\ClientInvoice;
use App\Claims\ClaimInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
            ->forDateRange($filters['start_date'], $filters['end_date'])
            ->forPayer($filters['payer_id'])
            ->forClient($filters['client_id']);

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

        $claim = $factory->createFromClientInvoice($clientInvoice);

        return new SuccessResponse('Claim has been created.', compact('claim'));
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
            return new SuccessResponse('Claim information has been saved.', new ClaimInvoiceResource($claim));
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to save this claim.  Please try again.');
    }

    /**
     * Create a ClaimInvoice.
     *
     * @param ClaimInvoice $claim
     * @param ClaimInvoiceFactory $factory
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(ClaimInvoice $claim, ClaimInvoiceFactory $factory)
    {
        $this->authorize('delete', $claim);

        try {
            $factory->deleteClaimInvoice($claim);
            return new SuccessResponse('Claim has been deleted.');
        } catch (CannotDeleteClaimInvoiceException $ex) {
            return new ErrorResponse(500, 'Could not delete this claim: ' . $ex->getMessage());
        }
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
    public function show(ClaimInvoice $claim, string $view = InvoiceViewFactory::HTML_VIEW)
    {
        $this->authorize('read', $claim);

        $groups = $claim->items->groupBy('type');

        if (! isset($groups['Expense'])) {
            $groups['Expense'] = [];
        }
        if (! isset($groups['Service'])) {
            $groups['Service'] = [];
        }

        return view('claims.claim_invoice', [
            'claim' => $claim,
            'sender' => $claim->business,
            'recipient' => $claim->payer,
            'client' => $claim->client,
            'itemGroups' => $groups,
        ]);

//            $pdfWrapper = app('snappy.pdf.wrapper');
//            $this->pdfWrapper->loadHTML($view->render());
//            return $this->pdfWrapper->download($this->filename);
    }
}
