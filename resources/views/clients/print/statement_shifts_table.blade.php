<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered shifts-table">
            <tbody>
            <tr class="bg-info">
                <th>Date</th>
                <th>Time</th>
                <th>EVV</th>
                <th width="35%">Activities Performed</th>
                <th>Caregiver</th>
                <th>Rate</th>
                <th>Hours</th>
                <th>Mileage</th>
                <th>Other Exp.</th>
                <th>Total</th>
            </tr>
            @if(isset($payment) && $payment->adjustment)
                <tr>
                    <td>{{ $payment->created_at->setTimezone($timezone)->format('m/d/Y') }}</td>
                    <td>Manual Adjustment</td>
                    <td colspan="3">{{ $payment->notes }}</td>
                    <td>${{ $payment->amount }}</td>
                </tr>
            @endif
            @foreach($shifts as $shift)
                <tr >
                    <td>
                        {{ $shift->checked_in_time->setTimezone($timezone)->format('m/d/Y') }}
                    </td>
                    <td style="white-space: nowrap">
                        {{ $shift->checked_in_time->setTimezone($timezone)->format('g:ia') }} -
                        {{ $shift->checked_out_time->setTimezone($timezone)->format('g:ia') }}
                    </td>
                    <td>{{ $shift->verified ? 'Yes' : 'No' }}</td>
                    <td>
                        {{ $shift->activities->implode('name', ', ') }}
                    </td>
                    <td>
                        {{ $shift->caregiver_name ?? $shift->caregiver->name }}
                    </td>
                    <td>
                        &dollar;{{ number_format($shift->hourly_total ?? $shift->costs()->getTotalHourlyCost(), 2) }}
                    </td>
                    <td>
                        {{ $shift->hours ?? $shift->duration }}
                    </td>
                    <td>
                        &dollar;{{ number_format($shift->mileage_costs ?? $shift->costs()->getMileageCost(), 2) }}
                    </td>
                    <td>
                        &dollar;{{ number_format($shift instanceof \App\Shift ? $shift->costs()->getOtherExpenses() : $shift->other_expenses, 2) }}
                    </td>
                    <td>
                        &dollar;{{ number_format($shift->shift_total ?? $shift->costs()->getTotalCost(), 2) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>