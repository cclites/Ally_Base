<?php

namespace App\Http\Controllers\Api\Quickbooks;

use App\Billing\ClientInvoice;
use App\Billing\Queries\OnlineClientInvoiceQuery;
use App\Business;
use App\Client;
use App\Http\Requests\Api\Quickbooks\QuickbooksDesktopApiRequest;
use App\Http\Controllers\Controller;
use App\QuickbooksClientInvoice;
use App\QuickbooksCustomer;
use App\QuickbooksInvoiceStatus;
use App\Responses\SuccessResponse;
use App\Services\Quickbooks\QuickbooksOnlineInvoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class QuickbooksDesktopController extends Controller
{
    /**
     * Pulse the API and make sure connection is working.
     *
     * @param QuickbooksDesktopApiRequest $request
     * @return QuickbooksApiResponse
     */
    public function ping(QuickbooksDesktopApiRequest $request)
    {
        $request->connection()->update([
            'last_connected_at' => Carbon::now(),
        ]);

        return new QuickbooksApiResponse('pong');
    }

    /**
     * Sync Customer and Service data.
     *
     * @param QuickbooksDesktopApiRequest $request
     * @return QuickbooksApiResponse
     */
    public function sync(QuickbooksDesktopApiRequest $request)
    {
        $customers = collect($request->customers);
        $services = collect($request->services);

        $this->syncCustomers($customers, $request->business());
        $this->syncServices($services, $request->business());

        $data = [
//            'customers' => $request->business()->quickbooksCustomers()->get(),
//            'services' => $request->business()->quickbooksServices()->get(),
        ];

        $message = "Synced {$customers->count()} customers and {$services->count()} services.";

        return new QuickbooksApiResponse($message, $data);
    }

    /**
     * Fetch invoices from processing queue.
     *
     * @param QuickbooksDesktopApiRequest $request
     * @param OnlineClientInvoiceQuery $invoiceQuery
     * @return QuickbooksApiResponse
     */
    public function fetchInvoices(QuickbooksDesktopApiRequest $request, OnlineClientInvoiceQuery $invoiceQuery)
    {
        $query = $invoiceQuery->with('items', 'quickbooksInvoice')
            ->forBusiness($request->business()->id)
            ->whereHas('quickbooksInvoice', function ($q) {
                return $q->where('status', QuickbooksInvoiceStatus::QUEUED());
            })
            ->latest();

        $invoices = $query->take(50)
            ->get()
            ->map(function (ClientInvoice $invoice) use ($request) {
                try {
                    return QuickbooksOnlineInvoice::fromClientInvoice($request->connection(), $invoice, false)
                        ->toDesktopArray();
                } catch (\Exception $ex) {
                    // Most likely a problem with determining the ally fee.
                    // This should not happen often.
                    app('sentry')->captureException($ex);
                    $invoice->quickbooksInvoice->updateStatus(
                        QuickbooksInvoiceStatus::ERRORED(), [
                            'errors' => 'Error determining the provider rate for one or more line items.  Please contact Ally.'
                        ]
                    );
                    return null;
                }
            })
            ->filter()
            ->toArray();

        return new QuickbooksApiResponse('', $invoices);
    }

    /**
     * Mark the given quickbooks invoices as processed.
     *
     * @param QuickbooksDesktopApiRequest $request
     * @return QuickbooksApiResponse
     */
    public function processInvoices(QuickbooksDesktopApiRequest $request)
    {
        $ids = $request->filled('ids') ? explode(',', $request->ids) : [];

        $invoices = QuickbooksClientInvoice::whereIn('id', $ids)
            ->where('business_id', $request->business()->id)
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->updateStatus(QuickbooksInvoiceStatus::PROCESSING());
        }

        return new QuickbooksApiResponse($invoices->count() . ' Invoices were marked as processed.');
    }

    /**
     * Log results from importing invoice attempts.
     *
     * @param QuickbooksDesktopApiRequest $request
     * @return QuickbooksApiResponse
     */
    public function invoiceResults(QuickbooksDesktopApiRequest $request)
    {
        $success = 0;
        $error = 0;
        foreach ($request->results as $result) {
            if (! isset($result['QuickbooksInvoiceId'])) {
                continue;
            }

            $qbInvoice = QuickbooksClientInvoice::where('id', $result['QuickbooksInvoiceId'])
                ->where('business_id', $request->business()->id)
                ->first();

            if (empty($qbInvoice)) {
                continue;
            }

            if (isset($result['Error'])) {
                $error++;
                $qbInvoice->updateStatus(QuickbooksInvoiceStatus::ERRORED(), ['errors' => $result['Error']]);
            }
            else if (isset($result['DesktopID'])) {
                $success++;
                $qbInvoice->updateStatus(QuickbooksInvoiceStatus::TRANSFERRED(), ['qb_desktop_id' => $result['DesktopID'], 'errors' => null]);
            }
        }

        return new QuickbooksApiResponse("$success invoices marked transferred, and $error invoices marked errored.");
    }

    /**
     * Sync the given collection of QB services with the
     * current data set.
     *
     * @param Collection $services
     * @param Business $business
     */
    protected function syncServices(Collection $services, Business $business)
    {
        // Find service records that no longer appear in Quickbooks.
        $deleteIds = $business->quickbooksServices()
            ->whereNotIn('service_id', $services->pluck('id'))
            ->get()
            ->pluck('id');

        // Remove any service mappings to those missing service records.
        $columns = ['shift_service_id', 'adjustment_service_id', 'refund_service_id', 'mileage_service_id', 'expense_service_id'];
        foreach ($columns as $column) {
            $business->quickbooksConnection()
                ->whereIn($column, $deleteIds)
                ->update([$column => null]);
        }

        // Delete the missing service records.
        $business->quickbooksServices()
            ->whereIn('id', $deleteIds)
            ->delete();

        foreach ($services as $service) {
            if ($match = $business->quickbooksServices()->where('service_id', $service['id'])->first()) {
                $match->update([
                    'name' => $service['name'],
                ]);
            } else {
                $business->quickbooksServices()->create([
                    'service_id' => $service['id'],
                    'name' => $service['name'],
                ]);
            }
        }
    }

    /**
     * Sync the given collection of QB customers with the
     * current data set.
     *
     * @param Collection $customers
     * @param Business $business
     */
    protected function syncCustomers(Collection $customers, Business $business)
    {
        // Find customer records that no longer appear in Quickbooks.
        $customerIds = $customers->pluck('id');
        $deleteIds = $business->quickbooksCustomers()
            ->whereNotIn('customer_id', $customerIds)
            ->get()
            ->pluck('id');

        // Remove any client->customer mappings to those missing customer records.
        Client::whereIn('quickbooks_customer_id', $deleteIds)
            ->update([
                'quickbooks_customer_id' => null,
            ]);

        // Delete the missing customer records.
        $business->quickbooksCustomers()
            ->whereIn('id', $deleteIds)
            ->delete();

        // Create OR update each customer record.
        foreach ($customers as $customer) {
            if ($match = $business->quickbooksCustomers()->where('customer_id', $customer['id'])->first()) {
                $match->update([
                    'name' => $customer['name'],
                ]);
            } else {
                $business->quickbooksCustomers()->create([
                    'customer_id' => $customer['id'],
                    'name' => $customer['name'],
                ]);
            }
        }
    }
}