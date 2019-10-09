<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var \App\Billing\Payer $recipient The related Payer
 * @var \App\Client $client The related Client
 */
?>
<div class="row print-header">
    <div class="header-left">
        @include('layouts.partials.print_logo')
        @if($sender->name())
            <div class="h4">Associated Home Care Company: {{ $sender->name() }}</div>
            <br>
            <div class="sender-address">
                @include('invoices.partials.address', ['address' => $sender->getAddress(), 'phone' => $sender->getPhoneNumber()])
            </div>
        @endif
    </div>
    <div class="text-right header-right">
        <div class="h1">Claim #{{ $claim->getName() }}</div>
        <br>
        <table class="header-right-table">
            <tr>
                <td>Claim Date: </td>
                <td>{{ $claim->getDate()->format('m/d/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Claim Status: </strong></td>
                <td>
                    {{ snake_to_title_case($claim->getStatus()) }}
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="row print-header">
    <div style="width: 50%; float:left">
        <table class="" style="margin-left: 3rem; margin: auto">
            <tr>
                <td colspan="2">
                    <strong>Bill To:</strong>
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
    <div style="width: 50%; float:right">
        <table class="" style="margin-right: 3rem; margin: auto">
            <tr>
                <td colspan="2">
                    <strong>Client:</strong>
                </td>
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
        </table>
    </div>
</div>
