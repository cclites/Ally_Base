<?php
/**
 * @var $sender \App\Contracts\ContactableInterface
 */
?>
<div class="row print-header">
    <div class="header-left">
        <div class="logo"><img src="{{ asset('/images/AllyLogo-new-light.png') }}" /></div>
        @if($sender->name())
            <div class="h4">Associated Home Care Company: {{ $sender->name() }}</div>
            <br>
            <div class="sender-address">
                @include('invoices.partials.address', ['address' => $sender->getAddress(), 'phone' => $sender->getPhoneNumber()])
            </div>
        @endif
    </div>
    <div class="text-right header-right">
        <div class="h1">Invoice #{{ $invoice->getName() }}</div>
        <br>
        <table class="header-right-table">
            <tr>
                <td>Invoice Date: </td>
                <td>{{ $invoice->getDate() }}</td>
            </tr>
            <tr>
                <td><strong>Invoice Status: </strong></td>
                <td>
                    @if ($invoice->getAmountDue() > 0)
                        <span style="color: darkred">Unpaid</span>
                    @else
                        <span style="color: green">Paid</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

@if ($subject->name())
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
                        <strong>{{ $recipient->name() }}</strong>
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
                        <strong>{{ $subject->name() }}</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        @include('invoices.partials.address', ['address' => $subject->getAddress(), 'phone' => $subject->getPhoneNumber()])
                    </td>
                </tr>
                @if( filled($subject->getBirthdate()))
                <tr>
                    <td colspan="2">
                        {{ \Carbon\Carbon::parse($subject->getBirthdate())->format('m/d/Y') }}
                    </td>
                </tr>
                @endif
                @if( filled($subject->getHic()))
                <tr>
                    <td colspan="2">
                        <strong>{{ $subject->getHic() }}</strong>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>
@endif