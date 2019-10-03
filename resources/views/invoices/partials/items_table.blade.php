<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered items-table">
            <thead>
            <tr class="bg-{{ isset($table_variant) ? $table_variant : 'info' }}">
                <th>Service Date</th>
                <th>Service Name</th>
                <th>Units</th>
                <th>Rate</th>
                <th>Total</th>
                <th>Amount Due by Payer</th>
            </tr>
            </thead>
            <tbody>
            @foreach($itemGroups as $group => $items)
                @if($group)
                    <tr>
                        <td colspan="6" class="item-group">
                            <strong>{{ $group }}</strong>
                        </td>
                    </tr>
                @elseif($itemGroups->count() > 1)
                    <tr>
                        <td colspan="6">
                            <strong>Other Items</strong>
                        </td>
                    </tr>
                @endif
                @foreach($items as $item)
                    <?php /** @var \App\Billing\ClientInvoiceItem $item */ ?>
                    <tr>
                        <td class="text-nowrap">{{ filter_date($item->date, 'm/d/Y') }}</td>
                        <td>
                            {{ $item->name }}
                            @if(trim($item->notes))
                                <br/>
                                <small>{{ $item->notes }}</small>
                            @endif
                        </td>
                        <td class="text-nowrap">{{ $item->units }}</td>
                        <td class="text-nowrap">{{ rate_format($item->rate) }}</td>
                        <td class="text-nowrap">{{ number_format($item->total, 2) }}</td>
                        <td class="text-nowrap">{{ number_format($item->amount_due, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th class="text-nowrap">{{ number_format($invoice->getItems()->sum('total'), 2) }}</th>
                <th class="text-nowrap">{{ number_format($invoice->amount, 2) }}</th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>