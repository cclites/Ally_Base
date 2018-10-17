@component('mail::message', ['header' => $business->name . ' - Powered by Ally'])
## Hello {{ $client->firstname }},

Your home care service week is finished. Please review and confirm your pending charge for home care visits. These visits will be charged to your account on file in 24 hours.

@component('mail::table')
| Date            | Caregiver            | Hours            | Rate             | Total             |
|-----------------|----------------------|------------------|------------------|-------------------|
@foreach ($shifts as $s)  | {{$s['date']}}  | {{ str_replace('*', '\*', $s['caregiver']) }}  | {{$s['hours']}}  | ${{$s['rate']}}  | ${{$s['total']}}  |
@endforeach
@endcomponent

## Total pending charge:  ${{ number_format($total, 2) }}   

<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding-right: 50px">
                                    <a href="{{ $confirmUrl }}" class="button button-green" target="_blank">Confirm</a>
                                </td>
                                <td style="padding-left: 50px">
                                    <a href="{{ $modifyUrl }}" class="button button-blue" target="_blank">Modify</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br/>

Thank you for choosing Ally!

<br/>
Sincerely,

The Ally Management Team

(800) 930-0587
@endcomponent
