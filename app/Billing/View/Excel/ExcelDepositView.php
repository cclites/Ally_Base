<?php
namespace App\Billing\View\Excel;

use App\Billing\BusinessInvoiceItem;
use App\Billing\CaregiverInvoiceItem;
use App\Billing\Deposit;
use App\Billing\View\Data\DepositInvoiceData;
use App\Billing\View\DepositViewStrategy;
use App\Contracts\ContactableInterface;
use App\Exports\GenericExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelDepositView implements DepositViewStrategy
{
    private $filename;

    function __construct(string $filename)
    {
        $this->filename = str_replace(['.xls', '.xlsx'], ['',''], $filename);
    }

    /**
     * @param \App\Contracts\ContactableInterface $recipient
     * @param \App\Billing\Deposit $deposit
     * @param \App\Billing\View\Data\DepositInvoiceData[] $invoiceObjects
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    function generate(ContactableInterface $recipient, Deposit $deposit, array $invoiceObjects)
    {
        $data = $this->mapToArray($invoiceObjects);

        return Excel::download(new GenericExport($data), $this->filename . '.xlsx');
    }

    /**
     * @param \App\Billing\View\Data\DepositInvoiceData[]  $invoiceObjects
     */
    public function mapToArray(array $invoiceObjects): array
    {
        /** @var \App\Billing\Contracts\InvoiceInterface[] $invoices */
        $invoices = array_map(function(DepositInvoiceData $data) {
            return $data->invoice();
        }, $invoiceObjects);

        $items = [];
        foreach($invoices as $invoice) {
            foreach($invoice->getItems() as $item) {
                $items[] = ($item instanceof CaregiverInvoiceItem)
                    ? $this->mapCaregiverItem($item)
                    : $this->mapBusinessItem($item);
            }
        }

        return collect($items)->sortBy('Date')->toArray();
    }

    public function mapBusinessItem(BusinessInvoiceItem $item)
    {
        return [
            'Date' => $item->date,
            'Group' => $item->group,
            'Client' => $item->getInvoiceable() ? optional($item->getInvoiceable()->getClient())->nameLastFirst() : null,
            'Caregiver' => $item->getInvoiceable() ? optional($item->getInvoiceable()->getCaregiver())->nameLastFirst() : null,
            'Service Name' => $item->name,
            'Units' => $item->units,
            'Client Rate' => $item->client_rate,
            'Caregiver Rate' => $item->caregiver_rate,
            'Ally Rate' => $item->ally_rate,
            'Reg Rate' => $item->rate,
            'Client Total' => multiply($item->client_rate, $item->units),
            'Caregiver Total' => multiply($item->caregiver_rate, $item->units),
            'Ally Total' => multiply($item->ally_rate, $item->units),
            'Reg Total' => $item->total,
            'Notes' => $item->notes,
        ];
    }

    public function mapCaregiverItem(CaregiverInvoiceItem $item)
    {
        return [
            'Date' => $item->date,
            'Group' => $item->group,
            'Client' => $item->getInvoiceable() ? optional($item->getInvoiceable()->getClient())->nameLastFirst() : null,
            'Service Name' => $item->name,
            'Units' => $item->units,
            'Rate' => $item->rate,
            'Total' => $item->total,
            'Notes' => $item->notes,
        ];
    }
}