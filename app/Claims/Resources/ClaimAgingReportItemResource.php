<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Claims\ClaimInvoice;
use Carbon\Carbon;

class ClaimAgingReportItemResource extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Claims\ClaimInvoice
     */
    public $resource;

    /** @var array */
    protected $period_current;
    /** @var array */
    protected $period_30_45;
    /** @var array */
    protected $period_46_60;
    /** @var array */
    protected $period_61_75;
    /** @var array */
    protected $period_75_plus;

    /**
     * ClaimAgingReportItemResource Constructor.
     *
     * @param mixed $resource
     * @return void
     */
    public function __construct(ClaimInvoice $resource)
    {
        $this->resource = $resource;

        $today = \Carbon\Carbon::now();
        $this->period_current = ['start' => $today->copy()->subDays(30), 'end' => $today->copy()];
        $this->period_30_45 = ['start' => $today->copy()->subDays(45), 'end' => $today->copy()->subDays(30)];
        $this->period_46_60 = ['start' => $today->copy()->subDays(60), 'end' => $today->copy()->subDays(46)];
        $this->period_61_75 = ['start' => $today->copy()->subDays(75), 'end' => $today->copy()->subDays(61)];
        $this->period_75_plus = ['start' => Carbon::parse('01/01/2018'), 'end' => $today->copy()->subDays(75)];
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'claim_id' => $this->resource->id,
            'claim_name' => $this->resource->name,
            'invoice_id' => $this->resource->clientInvoice->id,
            'invoice_name' => $this->resource->clientInvoice->name,
            'client_id' => $this->resource->client->id,
            'client_name' => $this->resource->client->nameLastFirst,
            'payer' => $this->resource->payer->name(),
            'current' => $this->inDateRange($this->period_current) ? $this->resource->getAmountDue() : 0.00,
            'period_30_45' => $this->inDateRange($this->period_30_45) ? $this->resource->getAmountDue() : 0.00,
            'period_46_60' => $this->inDateRange($this->period_46_60) ? $this->resource->getAmountDue() : 0.00,
            'period_61_75' => $this->inDateRange($this->period_61_75) ? $this->resource->getAmountDue() : 0.00,
            'period_75_plus' => $this->inDateRange($this->period_75_plus) ? $this->resource->getAmountDue() : 0.00,
        ];
    }

    /**
     * Check if the current Claim object was
     * created inside the given period.
     *
     * @param array $period
     * @return bool
     */
    protected function inDateRange(array $period): bool
    {
        return $this->resource->created_at->between($period['start'], $period['end']);
    }
}
