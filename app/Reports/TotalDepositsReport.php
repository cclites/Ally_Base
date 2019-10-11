<?php


namespace App\Reports;

use App\Billing\Deposit;
use App\Billing\Queries\DepositQuery;
use Carbon\Carbon;

class TotalDepositsReport extends BaseReport
{
    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var string
     */
    protected $start;

    /**
     * @var string
     */
    protected $end;

    /**
     * @var DepositQuery
     */
    protected $query;

    /**
     * TotalDepositsReport constructor.
     */
    public function __construct(DepositQuery $query)
    {
        $this->query = $query->with([
            'caregiver',
            'business',
            'chain',
        ]);
    }

    /**
     * @param $timezone
     * @return TotalDepositsReport
     */
    public function setTimezone($timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): self
    {
        return $this->query;
    }

    public function applyFilters( string $startdate, string $enddate ): self
    {
        $this->start = ( new Carbon( $startdate . ' 00:00:00', $this->timezone ) );
        $this->end   = ( new Carbon( $enddate . ' 23:59:59', $this->timezone ) );

        $this->query->whereBetween( 'created_at', [ $this->start, $this->end ] );

        return $this;
    }

    /**
     * @return Collection
     */
    protected function results(): iterable
    {
        return $this->query->get()->map(function(Deposit $deposit){

            //This is to account for older deposits that do not have a chain_id
            try{
                if($deposit->chain){
                    $name = $deposit->chain->name;
                } else if($deposit->deposit_type === "caregiver"){
                    $name = $deposit->caregiver->nameLastFirst . " (Caregiver)";
                } else if($deposit->deposit_type === "business"){
                    $name = $deposit->business->name . " (Business)";
                }
            }catch(\Exception $e){
                $name = 'Uncategorized - Deposit ID: ' . $deposit->id;
            }

            return [
                'name'=> $name,
                'amount'=>$deposit->amount,
                'business'=>$deposit->business,
            ];

        })->values();
    }


}