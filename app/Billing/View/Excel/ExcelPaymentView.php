<?php
namespace App\Billing\View\Excel;

use App\Billing\Payment;
use App\Billing\View\Data\PaymentInvoiceData;
use App\Billing\View\PaymentViewStrategy;
use App\Contracts\ContactableInterface;
use Maatwebsite\Excel\Facades\Excel;

class ExcelPaymentView implements PaymentViewStrategy
{
    private $filename;

    function __construct(string $filename)
    {
        $this->filename = str_replace(['.xls', '.xlsx'], ['',''], $filename);
    }

    /**
     * @param \App\Contracts\ContactableInterface $payer
     * @param \App\Billing\Payment $payment
     * @param \App\Billing\View\Data\PaymentInvoiceData[] $invoiceObjects
     * @return mixed
     */
    function generate(ContactableInterface $payer, Payment $payment, array $invoiceObjects)
    {
        Excel::create($this->filename, function($excel) use ($invoiceObjects) {

            $excel->sheet('Sheet1', function($sheet) use ($invoiceObjects) {
                $data = $this->mapToArray($invoiceObjects);
                $sheet->fromArray($data, null, 'A1', true);
            });

        })->export('xls');
    }

    /**
     * @param \App\Billing\View\Data\PaymentInvoiceData[]  $invoiceObjects
     */
    public function mapToArray(array $invoiceObjects): array
    {
        /** @var \App\Billing\Contracts\InvoiceInterface[] $invoices */
        $invoices = array_map(function(PaymentInvoiceData $data) {
            return $data->invoice();
        }, $invoiceObjects);

        $items = [];
        foreach($invoices as $invoice) {
            /** @var \App\Billing\BaseInvoiceItem $item */
            foreach($invoice->getItems() as $item) {
                $items[] = [
                    'Date' => $item->date,
                    'Group' => $item->group,
                    'Service Name' => $item->name,
                    'Units' => $item->units,
                    'Rate' => $item->rate,
                    'Total' => $item->total,
                    'Notes' => $item->notes,
                ];
            }
        }

        return $items;
    }
}