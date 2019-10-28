<?php


namespace App\Reports;

use App\Billing\Deposit;
use App\Billing\Queries\DepositQuery;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
     * @param DepositQuery $query
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
     * @return DepositQuery
     */
    public function query(): DepositQuery
    {
        return $this->query;
    }

    public function applyFilters(string $start, string $end): self
    {
        $this->start = (new Carbon($start . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $this->end = (new Carbon($end . ' 23:59:59', $this->timezone))->setTimezone('UTC');

        $this->query->whereBetween('created_at', [$this->start, $this->end]);

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function results(): Collection
    {
        return $this->query->get()->map(function (Deposit $deposit) {
            //This is to account for older deposits that do not have a chain_id
            $name = 'Uncategorized - Deposit ID: ' . $deposit->id;
            if (filled($deposit->chain)) {
                $name = $deposit->chain->name;
            } else if ($deposit->deposit_type === "caregiver") {
                $name = $deposit->caregiver->nameLastFirst . " (Caregiver)";
            } else if ($deposit->deposit_type === "business") {
                $name = $deposit->business->name . " (Business)";
            }

            return [
                'name' => $name,
                'amount' => $deposit->amount,
            ];
        })
            ->groupBy('name')
            ->map(function ($group) {
                return [
                    'name' => $group[0]['name'],
                    'amount' => $group->reduce(function ($carry, $group) {
                        return add($carry, floatval($group['amount']));
                    }, floatval(0.00))
                ];
            })
        ->values();
    }

    /**
     * Get the total amount from the report results.
     *
     * @return float
     */
    public function getTotalAmount() : float
    {
        return $this->rows()->reduce(function ($carry, $row) {
            return add($carry, floatval($row['amount']));
        }, floatval(0.00));
    }
}