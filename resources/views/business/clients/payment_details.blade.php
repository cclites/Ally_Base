@section('content')

<div>
    <b-table class="client_information">
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
                    Address: {{ $client->getBillingAddress() }}
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    City: {{ $client->user->addresses->where('type', 'home')->city }}
                </td>
                <td colspan="4">
                    State: {{ $client->user->addresses->where('type', 'home')->state }}
                </td>
                <td colspan="4">
                    Zip: {{ $client->user->addresses->where('type', 'home')->zip }}
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
    </b-table>

    <h2>Primary Payment Method</h2>

    {{--
      ACH PRIMARY
    --}}
    @if($client->default_payment_type === 'bank_accounts')
        <b-table class="primary_payment_ach">
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
                        Name on Account: {{ $client->getBillingName() }}
                    </td>
                    <td colspan="6">
                        Payment Frequency: Weekly
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        Payment Start Date: {{  }}
                    </td>
                    <td colspan="4">
                        Routing/ABA #: {{ $client->defaultPayment->last_four_routing_number }}
                    </td>
                    <td colspan="4">
                        Account # {{ $client->defaultPayment->last_four }}
                    </td>
                </tr>
            </tbody>
        </b-table>
    @endif

    {{--
        CC PRIMARY
    --}}

    @if($client->default_payment_type === 'credit_card')
        <b-table class="primary_payment_cc">
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
                    Name on Account: {{ $client->getBillingName() }}
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
                    Payment Start Date: {{  }}
                </td>
            </tr>

            <tr>
                <td colspan="8">
                    Billing Email: {{ $client->user->email }}
                </td>
                <td colspan="4">
                    Billing Phone: {{  }}
                </td>
            </tr>
            <tr>
                <td colspan="12">
                    Card Number: {{ $client->defaultPayment->last_four }}
                </td>
            </tr>

            </tbody>
        </b-table>
    @endif

    <h2>Backup Payment Method</h2>

    {{--
        ACH BACKUP PAYMENT
    --}}
    @if($client->backup_payment_type === 'bank_accounts')
        <b-table class="primary_payment_ach">
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
                    Name on Account: {{ $client->backupPayment->getBillingName() }}
                </td>
                <td colspan="6">
                    Payment Frequency: Weekly
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    Payment Start Date: {{  }}
                </td>
                <td colspan="4">
                    Routing/ABA #: {{ $client->backupPayment->last_four_routing_number }}
                </td>
                <td colspan="4">
                    Account # {{ $client->backupPayment->last_four }}
                </td>
            </tr>
            </tbody>
        </b-table>
    @endif

    {{--
        CC Backup Payment
    --}}
    @if($client->backup_payment_type)
        @if($client->backup_payment_type === 'credit_card')
            <b-table class="primary_payment_cc">
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
                            Name on Account: {{ $client->backupPayment->getBillingName() }}
                        </td>
                        <td colspan="6">
                            Payment Frequency: Weekly
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8">
                            Address: {{ $client->backupPayment->getBillingAddress() }}
                        </td>
                        <td colspan="4">
                            Payment Start Date: {{  }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8">
                            Billing Email: {{ $client->user->email }}
                        </td>
                        <td colspan="4">
                            Billing Phone: {{  }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="12">
                            Card Number: {{ $client->backupPayment->last_four }}
                        </td>
                    </tr>
                </tbody>
            </b-table>
        @endif
    @endif
</div>

@endsection