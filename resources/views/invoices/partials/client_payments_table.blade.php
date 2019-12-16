<?php
/**
 * @var \App\Billing\ClientInvoice $invoice
 * @var \Illuminate\Support\Collection|App\Billing\Payment[] $payments
 */
?>
<div class="row">
    <div class="col-md-12">

        <div class="h3">Applied Payments</div>

        <table class="table table-bordered payments-table">
            <thead>
            <tr class="bg-info">
                <th>Payment Date</th>
                <th>Payment Method</th>
                <th>Total Amount</th>
                <th>Amount Applied to Invoice</th>
            </tr>
            </thead>
            <tbody>
            @if(empty($payments) || $payments->count() == 0)
                <tr>
                    <td colspan="5" style="padding-left: 15px;">
                        None
                    </td>
                </tr>
            @endif
            @foreach($payments as $payment)
                <tr>
                    <td>{{ local_date($payment->getDate()) }}</td>
                    <td>{{ $payment->getType() }}</td>
                    <td>{{ number_format($payment->getAmount(), 2) }}</td>
                    <td>{{ number_format($payment->getAmountAppliedTowardsInvoice($invoice), 2) }}</td>
                </tr>
                @if($payment->getNotes())
                    <tr>
                        <td colspan="5" style="padding-left: 15px;">
                            <strong>Note: </strong> {{ $payment->getNotes() }}
                        </td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
</div>