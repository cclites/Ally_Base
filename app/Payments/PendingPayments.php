<?php


namespace App\Payments;

use App\Client;
use App\Shift;
use Carbon\Carbon;

class PendingPayments
{
    /**
     * @var Client[]
     */
    protected $clients;

    /**
     * @var static
     */
    protected $startDate;
    /**
     * @var static
     */
    protected $endDate;

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate->setTimezone('UTC');
        $this->endDate = $endDate->setTimezone('UTC');

        $this->clients = Client::whereHas('shifts', function($q) use ($startDate, $endDate) {
                $q->where('status', Shift::WAITING_FOR_CHARGE)
                    ->whereBetween('checked_in_time', [$startDate, $endDate]);
            })
            ->join('users', 'users.id', '=', 'clients.id')
            ->orderBy('users.lastname')
            ->orderBy('users.firstname')
            ->get();
    }

    public function getData()
    {
        $data = [];
        foreach($this->clients as $client) {
            $aggregator = new ClientPaymentAggregator($client, $this->startDate, $this->endDate);
            $data[] = $aggregator->getData();
        }
        return $data;
    }

}