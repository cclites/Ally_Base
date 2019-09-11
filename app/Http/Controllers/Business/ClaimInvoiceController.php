<?php

namespace App\Http\Controllers\Business;

use App\Billing\Claim;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;

class ClaimInvoiceController
{
    /**
     * Show claim invoice view.
     *
     * @param Claim $claim
     * @param string $view
     * @return \Illuminate\Http\Response
     */
    public function show(Claim $claim, string $view = InvoiceViewFactory::HTML_VIEW)
    {
        $strategy = InvoiceViewFactory::create($claim, $view);
        $viewGenerator = new InvoiceViewGenerator($strategy);
        return $viewGenerator->generateClaimInvoice($claim);
    }
}