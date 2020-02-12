@component('mail::message')
## Hello {{ $client->firstname }},

Your home care service week is finished. Please review and confirm your home care visits using the confirm or modify buttons below.

@component('mail::table')
| Date            | Caregiver            | Hours            | Rate             | Total             |
|-----------------|----------------------|------------------|------------------|-------------------|
@foreach ($shifts as $s)
| {{$s->date->setTimezone($timezone)->format('m/d/Y')}} | {{ str_replace('*', '\*', $s->caregiver) }} | {{$s->hours}} | {{is_numeric($s->rate) ? '$'.number_format($s->rate, 2) : $s->rate}}  | ${{number_format($s->total, 2)}}  |
@endforeach
| *TOTAL* | | {{ $shifts->bcsum('hours') }} | | {{ '$'.number_format($shifts->bcsum('total'), 2) }} |
@endcomponent


@if (app('settings')->get($client->business_id, 'allow_client_confirmations'))
<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <a href="{{ route('token-confirm-shifts', ['token' => $confirmToken]) }}" class="button button-blue" target="_blank">Confirm</a>
                                </td>
                                <td style="padding-left: 50px">
                                    <a href="{{ route('client.unconfirmed-shifts') }}" class="button button-red" target="_blank">Modify</a>
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

Your visit details and actual charge amount are subject to change by your home care company.  Your payment method on file will be charged within 24 hours.  Failure to take action may result in an incorrect bill amount.

Please do not reply to this email.  For any questions regarding this charge please contact your home care company.

<br/>

Thank you for choosing Ally!

<br/>
@include('emails.partials.signature')
@endcomponent
