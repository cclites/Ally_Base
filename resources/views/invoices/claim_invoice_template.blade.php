<?php
/**
 * @var \App\Billing\ClientInvoice $invoice
 * @var \App\Contracts\ContactableInterface $sender
 * @var \App\Contracts\ContactableInterface $recipient
 * @var \Illuminate\Support\Collection $itemGroups
 * @var \Illuminate\Support\Collection|App\Billing\Payment[] $payments
 */
?>
@extends( 'invoices.layout' )

@section( 'items' )

    <div class="row">

        <div class="col-md-12">

            <table class="table table-bordered items-table">

                <thead>

                    <tr class="bg-danger">

                        <th>Service Date</th>
                        <th>Service Name</th>
                        <th>Units</th>
                        <th>Rate</th>
                        <th>Total</th>
                        <th>Amount Due by Payer</th>
                    </tr>
                </thead>
                <tbody>

                @foreach( $itemGroups as $group => $items )

                    @if( $group )
                        <tr>

                            <td colspan="6" class="item-group">

                                <strong>{{ $group }}</strong>
                            </td>
                        </tr>
                    @elseif( $itemGroups->count() > 1 )

                        <tr>

                            <td colspan="6">

                                <strong>Other Items</strong>
                            </td>
                        </tr>
                    @endif

                    @foreach( $items as $item )
                        <?php /** @var \App\Billing\ClientInvoiceItem $item */ ?>
                        <tr>

                            <td class="text-nowrap">{{ local_date( $item->getShift()->checked_in_time ) }}</td>
                            <td>

                                {{ $item->getShiftName() }}
                                @if( trim( $item->claimable->notes ) )
                                    <br/>
                                    <small>{{ $item->claimable->notes }}</small>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $item->units }}</td>
                            <td class="text-nowrap">{{ rate_format($item->rate) }}</td>
                            <td class="text-nowrap">{{ number_format($item->amount, 2) }}</td>
                            <td class="text-nowrap">{{ number_format($item->amount_due, 2) }}</td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
                <tfoot>

                    <tr>

                        <th colspan="4">Total</th>
                        <th class="text-nowrap">{{ number_format( $invoice->getItems()->sum( 'amount' ), 2 ) }}</th>
                        <th class="text-nowrap">{{ number_format( $invoice->amount, 2 ) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@section('payments')
    <div class="row">
        <div class="col-md-12">

            <div class="h3">Applied Payments</div>

            <table class="table table-bordered payments-table">
                <thead>
                <tr class="bg-danger">
                    <th>Payment Date</th>
                    <th>Payment Method</th>
                    <th>Total Amount</th>
                    <th>Amount Applied to Invoice</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ local_date($payment->created_at) }}</td>
                        <td>{{ $payment->getPaymentMethod() }}</td>
                        <td>{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    @if($payment->notes)
                        <tr>
                            <td colspan="5" style="padding-left: 15px;">
                                <strong>Note: </strong> {{ $payment->notes }}
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection