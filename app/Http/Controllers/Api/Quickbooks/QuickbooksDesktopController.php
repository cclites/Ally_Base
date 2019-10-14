<?php

namespace App\Http\Controllers\Api\Quickbooks;

use App\Business;
use App\Client;
use App\Http\Requests\Api\Quickbooks\QuickbooksDesktopApiRequest;
use App\Http\Controllers\Controller;
use App\QuickbooksCustomer;
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

    public function syncServices(Collection $services, Business $business)
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

    public function syncCustomers(Collection $customers, Business $business)
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