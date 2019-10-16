<style>
    h5{
        margin-top: 20px;
    }

    table{
        width: 100%;
        border-radius: 8px;
    }

    table, th, td {
        border: 1px solid #93a1a1;
        border-collapse: collapse;
    }

    th, td{
        padding: 6px;
    }

    th{
        font-size: 18px;
        color: #ffffff;
        background-color: #0b67cd;
    }
</style>

@php
    $expiration = new Carbon('next tuesday');
    $expiration = $expiration->format('m/d/Y');

    $mask = "******";
@endphp

<div style="margin-top: 20px;">
    <table class="client_information">
        <thead>
            <tr>
                <th colspan="12">Client Information:</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6">
                    First: {{ $client->first_name }}
                </td>
                <td colspan="6">
                    Last: {{ $client->last_name }}
                </td>
            </tr>
            <tr>
                <td colspan="12">
                    Address: {{ $client->getBillingAddress()->address1 }}
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    City: {{ $client->getBillingAddress()->city }}
                </td>
                <td colspan="4">
                    State: {{ $client->getBillingAddress()->state }}
                </td>
                <td colspan="4">
                    Zip: {{ $client->getBillingAddress()->zip }}
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    Email: {{ $client->email }}
                </td>
                <td colspan="6">
                    SSN: {{ $client->masked_ssn }}
                </td>
            </tr>

        </tbody>
    </table>

    <h5>Primary Payment Method</h5>

    {{--
      ACH PRIMARY
    --}}
    @if($client->default_payment_type === 'bank_accounts')
        <table class="primary_payment_ach">
            <thead>
                <th colspan="12">ACH</th>
            </thead>
            <tbody>
                <tr>
                    <td colspan="12">
                        Type: {{ $client->getPaymentType() === 'checking' ? "Checking" : "Savings" }}
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        Name on Account: {{ $client->defaultPayment->name_on_account }}
                    </td>
                    <td colspan="6">
                        Payment Frequency: Weekly
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        Payment Start Date: {{ $expiration }}
                    </td>
                    <td colspan="4">
                        Routing/ABA #: {{ $mask . $client->defaultPayment->last_four_routing_number }}
                    </td>
                    <td colspan="4">
                        Account #: {{ $mask . $client->defaultPayment->last_four }}
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    {{--
        CC PRIMARY
    --}}

    @if($client->default_payment_type !== 'bank_accounts')
        <table class="primary_payment_cc">
            <thead>
            <th colspan="12">Credit Card/Debit Card</th>
            </thead>
            <tbody>
            <tr>
                <td colspan="12">
                    Type: {{ ucfirst($client->defaultPayment->type) }}
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    Name on Account: {{ $client->defaultPayment->name_on_card }}
                </td>
                <td colspan="6">
                    Payment Frequency: Weekly
                </td>
            </tr>

            <tr>
                <td colspan="8">
                    Address: {{ $client->getBillingAddress() }}
                </td>
                <td colspan="4">
                    Payment Start Date: {{ $expiration }}
                </td>
            </tr>

            <tr>
                <td colspan="8">
                    Billing Email: {{ $client->user->email }}
                </td>
                <td colspan="4">
                    Billing Phone: {{ $client->phoneNumbers()->where('type', 'service')->first() }}
                </td>
            </tr>
            <tr>
                <td colspan="12">
                    Card Number: {{ $mask . $client->defaultPayment->last_four }}
                </td>
            </tr>

            </tbody>
        </table>
    @endif

    <h5>Backup Payment Method</h5>

    {{--
        ACH BACKUP PAYMENT
    --}}
    @if($client->backup_payment_type === 'bank_accounts')
        <table class="primary_payment_ach">
            <thead>
            <th colspan="12">ACH</th>
            </thead>
            <tbody>
            <tr>
                <td colspan="12">
                    Type: {{ $client->backup_payment_type === 'checking' ? "Checking" : "Savings" }}
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    Name on Account: {{ $client->backupPayment->name_on_account }}
                </td>
                <td colspan="6">
                    Payment Frequency: Weekly
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    Payment Start Date: {{ $expiration }}
                </td>
                <td colspan="4">
                    Routing/ABA #: {{ $mask . $client->backupPayment->last_four_routing_number }}
                </td>
                <td colspan="4">
                    Account #: {{ $mask . $client->backupPayment->last_four }}
                </td>
            </tr>
            </tbody>
        </table>
    @endif

    {{--
        CC Backup Payment
    --}}
    @if($client->backup_payment_type)
        @if($client->backup_payment_type !== 'bank_accounts')
            <table class="primary_payment_cc">
                <thead>
                <th colspan="12">Credit Card/Debit Card</th>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="12">
                            Type: {{ ucfirst($client->backupPayment->type) }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6">
                            Name on Account: {{ $client->backupPayment->name_on_card }}
                        </td>
                        <td colspan="6">
                            Payment Frequency: Weekly
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8">
                            Address: {{ $client->backupPayment->getBillingAddress()->full_address }}
                        </td>
                        <td colspan="4">
                            Payment Start Date: {{ $expiration }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8">
                            Billing Email: {{ $client->user->email }}
                        </td>
                        <td colspan="4">
                            Billing Phone: {{ $client->backupPayment->getBillingPhone() }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="12">
                            Card Number: {{ $mask . $client->backupPayment->last_four }}
                        </td>
                    </tr>
                </tbody>
            </table>
        @endif
    @endif
</div>
