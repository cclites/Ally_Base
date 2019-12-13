@if($invoice->getClientPayer() && $invoice->getClientPayer()->getExtraInvoiceData())
    <div class="h3">Notes:</div>
    @foreach($invoice->getClientPayer()->getExtraInvoiceData() as $data)
        <p>{{ $data }}</p>
    @endforeach
@endif
