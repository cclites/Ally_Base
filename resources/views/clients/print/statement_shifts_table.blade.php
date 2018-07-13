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
                    <td>
                        {{ $shift->checked_in_time->setTimezone($timezone)->format('g:ia') }} -
                        {{ $shift->checked_out_time->setTimezone($timezone)->format('g:ia') }}
                    </td>
                    <td>{{ $shift->EVV ? 'Yes' : 'No' }}</td>
                    <td>
                        <div>{{ $shift->activities->implode(', ') }}</div>
                        {{--@foreach($shift->activities as $activity)--}}
                        {{--<div>{{ $activity }}</div>--}}
                        {{--@endforeach--}}
                    </td>
                    <td>
                        {{ $shift->caregiver_name }}
                    </td>
                    <td>
                        ${{ $shift->hourly_total }}
                    </td>
                    <td>
                        {{ $shift->hours }}
                    </td>
                    <td>
                        &dollar;{{ $shift->shift_total }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>