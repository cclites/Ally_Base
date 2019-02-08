<?php
namespace App\Responses\Resources;

use Illuminate\Http\Resources\Json\Resource;

class DepositLog extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Billing\DepositLog
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $invoices = collect();
        if ($this->resource->deposit) {
            $invoices = $this->resource->deposit->caregiverInvoices->merge(
                $this->resource->deposit->businessInvoices
            );
        }

        $recipient = optional($this->resource->deposit)->getRecipient();

        return [
            'log_id' => $this->resource->id,
            'batch_id' => $this->resource->batch_id,
            'deposit_id' => $this->resource->deposit_id,
            'recipient' => $recipient ? $recipient->name() : null,
            'payment_method' => optional($this->resource->method)->getDisplayValue(),
            'amount' => $this->resource->deposit->amount ?? null,
            'success' => $this->resource->deposit->success ?? false,
            'exception' => $this->resource->exception,
            'error_message' => $this->resource->error_message,
            'invoices' => DepositInvoice::collection($invoices),
        ];
    }
}