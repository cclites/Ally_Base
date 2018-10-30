@component('mail::message', ['header' => $businessName . ' - Powered by Ally'])
## Hello {{ $client->firstname }},

Your home care service week is finished. Please review and confirm your pending charge for home care visits. These visits will be charged to your account on file in 24 hours.

@component('mail::table')
| Date            | Caregiver            | Hours            | Rate             | Total             |
|-----------------|----------------------|------------------|------------------|-------------------|
@foreach ($shifts as $s)  | {{$s->date->format('m/d/Y')}}  | {{ str_replace('*', '\*', $s->caregiver) }}  | {{$s->hours}}  | ${{number_format($s->rate, 2)}}  | ${{number_format($s->total, 2)}}  |
@endforeach
@endcomponent


## <center>Total pending charge:  ${{ number_format($total, 2) }}</center>

@if ($client->business->allow_client_confirmations)
<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <a href="{{ route('token-confirm-shifts', ['token' => $confirmToken]) }}" class="button button-green" target="_blank">Confirm</a>
                                </td>
                                <td style="padding-left: 50px">
                                    <a href="{{ route('client.unconfirmed-shifts') }}" class="button button-blue" target="_blank">Modify</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif

Your payment method on file will be charged within 24 hours.

Please do not reply to this email.

<br/>

Thank you for choosing Ally!

<br/>
Sincerely,

The Ally Management Team

(800) 930-0587
@endcomponent
