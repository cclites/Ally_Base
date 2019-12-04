<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var \App\Billing\Payer $recipient The related Payer
 * @var \App\Claims\Contracts\ClaimableInterface $firstClaimable The first claimable item (use for Client info)
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
                    <strong>{{ $client->name() }}</strong>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    @include('invoices.partials.address', ['address' => $client->getAddress(), 'phone' => $client->getPhoneNumber()])
                </td>
            </tr>
            @if(isset($client_ltci_policy_number))
            <tr>
                <td><strong>Policy #: </strong></td>
                <td>
                    {{ $client_ltci_policy_number }}
                </td>
            </tr>
            @endif
            @if(isset($client_ltci_claim_number))
            <tr>
                <td><strong>Claim #: </strong></td>
                <td>
                    {{ $client_ltci_claim_number }}
                </td>
            </tr>
            @endif
            @if(isset($client_program_number))
            <tr>
                <td><strong>Program ID: </strong></td>
                <td>
                    {{ $client_program_number }}
                </td>
            </tr>
            @endif
            @if(isset($client_cirts_number))
            <tr>
                <td><strong>CIRTS ID: </strong></td>
                <td>
                    {{ $client_cirts_number }}
                </td>
            </tr>
            @endif
        </table>
    </div>
</div>
