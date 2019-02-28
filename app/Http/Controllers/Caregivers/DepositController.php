<?php
namespace App\Http\Controllers\Caregivers;

use App\Billing\Deposit;
use App\Billing\Queries\DepositQuery;
use App\Billing\View\Html\HtmlDepositView;
use App\Billing\View\DepositViewGenerator;
use App\Billing\View\Pdf\PdfDepositView;

class DepositController extends BaseController
{
    /**
     * @var \App\Billing\Queries\DepositQuery
     */
    protected $depositQuery;

    public function __construct(DepositQuery $depositQuery)
    {
        $this->depositQuery = $depositQuery;
    }

    public function index()
    {
        $caregiver = $this->caregiver();
        $deposits = $this->depositQuery->forCaregiver($caregiver)->get();
        return view('caregivers.deposit_history', compact('caregiver', 'deposits'));
    }

    public function show(Deposit $deposit, string $view = "html")
    {
        $this->authorize('read', $deposit);

        $strategy = new HtmlDepositView();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfDepositView('deposit-' . $deposit->id . '.pdf');
        }

        $viewGenerator = new DepositViewGenerator($strategy);
        return $viewGenerator->generate($deposit);
    }
}