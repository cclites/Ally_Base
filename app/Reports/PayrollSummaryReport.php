<?php


namespace App\Reports;


use App\Billing\Deposit;

use App\Business;
use Carbon\Carbon;
use App\Client;

class PayrollSummaryReport extends BusinessResourceReport
{
    /**
     * @var \Eloquent
     */
    protected $query;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var string
     */
    protected $clientType;


    /**
     * PayrollSummaryReport constructor.
     */
    public function __construct()
    {
        $this->query = Deposit::query()
                        ->where('deposit_type','caregiver')
                        ->with([
                                'shifts.client',
                                'caregiver'
                        ]);
    }


    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): self
    {
        return $this->query;
    }

    /**
     * Set instance timezone
     *
     * @param string $timezone
     * @return PayrollSummaryReport
     */
    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function applyFilters(string $start, string $end, int $business, ?string $client_type, ?int $caregiver): self
    {


        $startDate = new Carbon($start . ' 00:00:00', $this->timezone);
        $endDate = new Carbon($end . ' 23:59:59', $this->timezone);


        $this->query->whereBetween('created_at', [$startDate, $endDate]);

        $this->query->forBusinesses([$business]);

        if(filled($client_type)){
            $this->clientType = $client_type;
            $this->query->whereHas('shifts.client', function($q) use($client_type){
                $q->where('client_type', $client_type);
            });
        }

        if(filled($caregiver)){
            $this->query->whereCaregiverId($caregiver);
        }

        return $this;
    }

    protected function results() : ?iterable
    {

        return $this->query->get()
                ->map(function(Deposit $deposit){
                    return [
                            'amount'=>$deposit->amount,
                            'caregiver'=>$deposit->caregiver->nameLastFirst(),
                            'type'=>$this->clientType ? ucwords(str_replace("_", " ", $this->clientType)) : 'All Types',
                            'date'=> (new Carbon($deposit->created_at))->format('m/d/Y')
                        ];

                })->values();

    }
}