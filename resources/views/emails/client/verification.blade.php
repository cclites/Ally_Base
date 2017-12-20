@component('mail::message')
## Hello!

We are now processing transactions on behalf of {{ $business->name }}.  To ensure no interruption in your care service, please follow the link below to confirm your information.

@component('mail::button', ['url' => $url])
    Click here to confirm
@endcomponent

Sincerely,

The Ally Management Team

(800) 930-0587
@endcomponent
