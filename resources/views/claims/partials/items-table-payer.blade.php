<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var \App\Billing\Payer $recipient The related Payer
 * @var \Illuminate\Support\Collection $itemGroups
 */
?>
<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered items-table">
            <thead>
                <tr class="bg-danger">
                    <th>Item</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Client</th>
                    <th>Caregiver</th>
                    <th>Units</th>
                    <th>Rate</th>
                    <th>Total</th>
                    <th>Amount Due</th>
                </tr>
            </thead>
            <tbody>

            {{-- Group by client name --}}
            @foreach($itemGroups as $clientGroup)
                @foreach ($clientGroup['items'] as $item)
                <?php
                    /** @var \App\Claims\ClaimInvoiceItem $item */
                    /** @var \App\Claims\Contracts\ClaimableInterface $claimable */
                    $claimable = $item->claimable;
                ?>
                <tr>
                    @if ($item->claimable_type == \App\Claims\ClaimableService::class)
                        <td>{{ $claimable->getName() }}</td>
                        <td class="text-nowrap">{{ local_date($item->date, 'm/d/Y', auth()->user()->getTimezone()) }}</td>
                        <td class="text-nowrap">
                            {{ local_date($claimable->getStartTime(), 'h:i A', auth()->user()->getTimezone()) }}
                            -
                            {{ local_date($claimable->getEndTime(), 'h:i A', auth()->user()->getTimezone()) }}
                        </td>
                    @else
                        {{-- Expenses --}}
                        <td>Expense: {{ $claimable->getName() }}</td>
                        <td class="text-nowrap">{{ local_date($item->date, 'm/d/Y', auth()->user()->getTimezone()) }}</td>
                        <td class="text-nowrap">{{ local_date($item->date, 'H:i A', auth()->user()->getTimezone()) }}</td>
                    @endif
                    <td class="text-nowrap">{{ $item->getClientName() }}</td>
                    <td class="text-nowrap">{{ $item->getCaregiverName() }}</td>
                    <td class="text-nowrap">{{ $item->units }}</td>
                    <td class="text-nowrap">{{ rate_format($item->rate) }}</td>
                    <td class="text-nowrap">{{ number_format($item->amount, 2) }}</td>
                    <td class="text-nowrap">{{ number_format($item->amount_due, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <th colspan="5" style="text-align: right">
                        <strong>Charges for {{ $clientGroup['client'] }}:</strong>
                    </th>
                    <th class="text-nowrap">{{ number_format( $clientGroup['units'], 2) }}</th>
                    <th class="text-nowrap"></th>
                    <th class="text-nowrap">{{ number_format( $clientGroup['amount'], 2) }}</th>
                    <th class="text-nowrap">{{ number_format( $clientGroup['amount_due'], 2) }}</th>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="7">
                        <strong>Total</strong>
                    </th>
                    <th class="text-nowrap">{{ number_format( $claim->amount, 2) }}</th>
                    <th class="text-nowrap">{{ number_format( $claim->amount_due, 2 ) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
