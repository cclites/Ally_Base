@if($invoice->clientPayer->payer->notes)
    <p>{{ $invoice->clientPayer->payer->notes }}</p>
@endif

 {{ $invoice->clientPayer->payer->notes }}

<div class="h3">Notes:</div>

<p>Notes Go Here!!</p>