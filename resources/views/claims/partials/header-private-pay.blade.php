<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var \App\Billing\Payer $recipient The related Payer
 * @var array $clientData Client print on invoice data
 */
?>
<div class="row print-header">
    <div class="header-left">
        @include('claims.partials.business-info')
    </div>
    <div class="text-right header-right">
        <div class="h2">C-Invoice #{{ $claim->getName() }}</div>
        <br>

        <table class="header-right-table">
            <tr>
                <td><strong>Invoice Date: </strong></td>
                <td>{{ $claim->getDate()->format('m/d/Y') }}</td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>{{ $client->name() }}</strong>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    @include('invoices.partials.address', ['address' => $client->getAddress(), 'phone' => $client->getPhoneNumber()])
                </td>
            </tr>
            @foreach($clientData as $data)
            <tr>
                <td colspan="2">
                    {{ $data }}
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
