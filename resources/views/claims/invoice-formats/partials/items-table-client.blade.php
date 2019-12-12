<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var \App\Billing\Payer $recipient The related Payer
 * @var \Illuminate\Support\Collection $itemGroups
 */
?>
@section('items')
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered items-table">
                <thead>
                    <tr class="bg-danger">
                        <th>Item</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Caregiver</th>
                        <th>Units</th>
                        <th>Rate</th>
                        <th>Total</th>
                        <th>Amount Due</th>
                    </tr>
                </thead>
                <tbody>

                <tr>
                    <td colspan="8" class="item-group">
                        <strong>Services</strong>
                    </td>
                </tr>
                @foreach( $itemGroups['Service'] as $item )
                    <?php
                        /** @var \App\Claims\ClaimInvoiceItem $item */
                        /** @var \App\Claims\Contracts\ClaimableInterface $claimable */
                        $claimable = $item->claimable;
                    ?>
                    <tr>
                        <td>{{ $claimable->getName() }}</td>
                        <td class="text-nowrap">{{ local_date($item->date, 'm/d/Y', auth()->user()->getTimezone()) }}</td>
                        <td class="text-nowrap">
                            {{ local_date($claimable->getStartTime(), 'h:i A', auth()->user()->getTimezone()) }}
                            -
                            {{ local_date($claimable->getEndTime(), 'h:i A', auth()->user()->getTimezone()) }}
                        </td>
                        <td class="text-nowrap">{{ $item->getCaregiverName() }}</td>
                        <td class="text-nowrap">{{ $item->units }}</td>
                        <td class="text-nowrap">{{ rate_format($item->rate) }}</td>
                        <td class="text-nowrap">{{ number_format($item->amount, 2) }}</td>
                        <td class="text-nowrap">{{ number_format($item->amount_due, 2) }}</td>
                    </tr>
                @endforeach

                @if(filled($itemGroups['Expense']))
                <tr>
                    <td colspan="8" class="item-group">
                        <strong>Expenses</strong>
                    </td>
                </tr>
                @endif
                @foreach( $itemGroups['Expense'] as $item )
                    <?php
                        /** @var \App\Claims\ClaimInvoiceItem $item */
                        /** @var \App\Claims\Contracts\ClaimableInterface $claimable */
                        $claimable = $item->claimable;
                    ?>
                    <tr>
                        <td>{{ $claimable->getName() }}</td>
                        <td class="text-nowrap">{{ local_date($item->date, 'm/d/Y', auth()->user()->getTimezone()) }}</td>
                        <td class="text-nowrap">{{ local_date($item->date, 'H:i A', auth()->user()->getTimezone()) }}</td>
                        <td class="text-nowrap">{{ $item->getCaregiverName() }}</td>
                        <td class="text-nowrap">{{ $item->units }}</td>
                        <td class="text-nowrap">{{ rate_format($item->rate) }}</td>
                        <td class="text-nowrap">{{ number_format($item->amount, 2) }}</td>
                        <td class="text-nowrap">{{ number_format($item->amount_due, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6">
                            <strong>Total</strong>
                        </th>
                        <th class="text-nowrap">{{ number_format( $claim->amount, 2) }}</th>
                        <th class="text-nowrap">{{ number_format( $claim->amount_due, 2 ) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
