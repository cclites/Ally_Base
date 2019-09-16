
<div class="row">
    <div class="footer-left">
        &nbsp;
        {{--<p>This is a statement. Your payment was processed on {{ $payment->created_at->setTimezone($timezone)->format('m/d/Y') }} using your payment information on file.</p>--}}
    </div>
    <div class="footer-right" style="padding-right: 15px;">
        <table class="table">
            <tbody>
            <tr>
                <th>Invoiced Amount:</th>
                <td>
                    &dollar;{{ number_format($invoice->getAmount(), 2) }}
                </td>
            </tr>
            <tr>
                <th>Amount Due:</th>
                <td>
                    &dollar;{{ number_format($invoice->getAmountDue(), 2) }}
                </td>
            </tr>
            @if ($invoice->getAmountDue() <= 0)
            <tr>
                <td colspan="2">
                    <div class="h2">NOTHING DUE - THIS INVOICE HAS BEEN PAID</div>
                </td>
            </tr>
            @endif

            </tbody>
        </table>
    </div>
</div>

<div class="row mt-5">
    <div class="col">
        @if (! empty($sender->getPhoneNumber()))
        <p class="text-center"><em>For questions regarding hours and rates: {{ $sender->getPhoneNumber()->number() }}</em></p>
        @endif
        <p class="text-center">
            For questions regarding payments: support@allyms.com - (800) 930-0587
        </p>
        <p class="text-center"><em>Thank you for your business!</em></p>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="pull-right" style="margin-right: 1rem;">&copy; {{ \Carbon\Carbon::now()->year }} Ally</div>
    </div>
</div>