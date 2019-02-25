<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered items-table">
            <thead>
            <tr class="bg-info">
                <th>Service Date</th>
                <th>Service Name</th>
                <th>Units</th>
                <th>Rate</th>
                <th>Total</th>
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
                    <?php /** @var \App\Billing\CaregiverInvoiceItem $item */ ?>
                    <tr>
                        <td>{{ local_date($item->date) }}</td>
                        <td>
                            {{ $item->name }}
                            @if(trim($item->notes))
                                <br/>
                                <small>{{ $item->notes }}</small>
                            @endif
                        </td>
                        <td>{{ $item->units }}</td>
                        <td>{{ rate_format($item->rate) }}</td>
                        <td>{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th>{{ number_format($invoice->amount, 2) }}</th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>