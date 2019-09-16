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
            @foreach($payments as $payment)
                <tr>
                    <td>{{ local_date($payment->created_at) }}</td>
                    <td>{{ $payment->payment_type }}</td>
                    <td>{{ number_format($payment->amount, 2) }}</td>

                    @if(!is_array($payment->pivot))
                        <td>{{ number_format($payment->pivot->amount_applied ?? '-1', 2) }}</td>
                    @else
                        <td>{{ number_format($payment->pivot['amount_applied'] ?? '-1', 2) }}</td>
                    @endif
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