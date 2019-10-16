@component('mail::message')
    ## Hello!

    @if($type === 'client')
        Please be aware that we will be charging your payment method on file for senior care services.
    @else
        Please be aware that we will be depositing a payment into your account for senior care services.
    @endif

    You can login to view your statements here: https://app.allyms.com

    Please contact us if you have any questions.

    support@allyms.com
    1 (800) 930-0587
    Business Hours:
    Monday - Friday
    9am - 5pm Eastern
    
@endcomponent