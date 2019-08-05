@if($invoice->getClientPayer() && $invoice->getClientPayer()->notes)
    <div class="h3">Notes:</div>
    <p>{{ $invoice->getClientPayer()->notes }}</p>
@endif
