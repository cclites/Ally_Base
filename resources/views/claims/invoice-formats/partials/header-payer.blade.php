<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var \App\Billing\Payer $recipient The related Payer
 */
?>
<div class="row print-header">
    <div class="header-left">
        @include('claims.invoice-formats.partials.business-info')
    </div>
    <div class="text-right header-right">
        <div class="h1">C-Invoice #{{ $claim->getName() }}</div>
        <br>

        <table class="header-right-table">
            <tr>
                <td><strong>Claim Date: </strong></td>
                <td>{{ $claim->getDate()->format('m/d/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Claim Status: </strong></td>
                <td>
                    {{ snake_to_title_case($claim->getStatus()) }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>{{ $claim->payer_name }}</strong>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    @include('invoices.partials.address', ['address' => $recipient->getAddress(), 'phone' => $recipient->getPhoneNumber()])
                </td>
            </tr>
        </table>
    </div>
</div>
