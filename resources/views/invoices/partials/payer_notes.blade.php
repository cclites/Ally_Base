@if($invoice->getClientPayer() && $invoice->getClientPayer()->notes)
    <div class="h3">Notes:</div>
    <p>{{ $invoice->getClientPayer()->notes }}</p>

    @foreach($recipient->getExtraInvoiceData() as $data)
        <p>{{ $data }}</p>
    @endforeach
    @foreach($subject->getExtraInvoiceData() as $data)
        <p>{{ $data }}</p>
    @endforeach
    @if(filled($invoice->getClientPayer()))
        @foreach($invoice->getClientPayer()->getExtraInvoiceData() as $data)
            <p>{{ $data }}</p>
        @endforeach
    @endif
@endif
