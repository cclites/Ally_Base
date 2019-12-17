@include('layouts.partials.print_logo')
@if($sender->name())
    <div class="h4">Associated Home Care Company: {{ $sender->name() }}</div>
    <br>
    <div class="sender-address">
        @include('invoices.partials.address', ['address' => $sender->getAddress(), 'phone' => $sender->getPhoneNumber()])
    </div>
@endif

<table class="header-left-table">
    @if($sender->getEinNumber())
    <tr>
        <td><strong>Business EIN:</strong></td>
        <td>{{ $sender->getEinNumber() }}</td>
    </tr>
    @endif

    @if($sender->getNpiNumber())
    <tr>
        <td><strong>NPI Number:</strong></td>
        <td>{{ $sender->getNpiNumber() }}</td>
    </tr>
    @endif

    @if($sender->getMedicaidLicenseNumber())
    <tr>
        <td><strong>License #:</strong></td>
        <td>{{ $sender->getMedicaidLicenseNumber() }}</td>
    </tr>
    @endif
</table>