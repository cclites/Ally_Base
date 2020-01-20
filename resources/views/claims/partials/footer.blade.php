<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var array $notes Client payer notes
 */
?>
<div class="row">
    <div class="footer-left">
        @if(filled($notes))
            <div class="h3">Notes:</div>
            @foreach($notes as $data)
                <p>{{ $data }}</p>
            @endforeach
        @endif
    </div>
    <div class="footer-right" style="padding-right: 15px; padding-top: 20px;">
        <table class="table">
            <tbody>
            <tr>
                <th>Invoiced Amount:</th>
                <td>
                    &dollar;{{ number_format($claim->getAmount(), 2) }}
                </td>
            </tr>
            <tr>
                <th>Amount Due:</th>
                <td>
                    &dollar;{{ number_format($claim->getAmountDue(), 2) }}
                </td>
            </tr>
            @if ($claim->getAmountDue() === floatval(0))
            <tr>
                <td colspan="2">
                    <div class="h2">NOTHING DUE - THIS CLAIM HAS BEEN PAID</div>
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
        <p class="text-center"><em>For questions call {{ $sender->getPhoneNumber()->number() }}</em></p>
        @endif
        <p class="text-center"><em>Thank you for your business!</em></p>
    </div>
</div>

<div class="row" style="page-break-after: always;">
    <div class="col">
        <div class="pull-right" style="margin-right: 1rem;">&copy; {{ \Carbon\Carbon::now()->year }} Ally</div>
    </div>
</div>