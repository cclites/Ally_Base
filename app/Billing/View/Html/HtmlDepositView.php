<?php
namespace App\Billing\View\Html;

use App\Billing\Deposit;
use App\Billing\View\DepositViewStrategy;
use App\Contracts\ContactableInterface;

class HtmlDepositView implements DepositViewStrategy
{
    private $view;

    function __construct(string $view = "statements.deposit")
    {
        $this->view = $view;
    }

    /**
     * @param \App\Contracts\ContactableInterface $recipient
     * @param \App\Billing\Deposit $deposit
     * @param \App\Billing\View\Data\DepositInvoiceData[] $invoiceObjects
     * @return mixed
     */
    function generate(ContactableInterface $recipient, Deposit $deposit, array $invoiceObjects)
    {
        $render = 'html';
        return response(view($this->view, compact("recipient", "deposit", "invoiceObjects", 'render')));
    }
}