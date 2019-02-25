@component('mail::message')
## Welcome to Ally,

We are now processing transactions on behalf of **{{ $businessChain->name }}**.  

@if ($caregiver->setup_status == 'confirmed_profile')

It looks like you need to setup an account.  Follow the link below to setup a username and password for our system. From there you can manage your account details including your payment method.

@component('mail::button', ['url' => $url])
    Setup my account
@endcomponent

@elseif ($caregiver->setup_status == 'created_account')

It looks like you already have a username and password to our system, but there are no pay details on file.  Please login below and add pay details.

@component('mail::button', ['url' => $url])
    Add payment information
@endcomponent

@else

To ensure no interruption in your pay, please follow the link below to enter your information and to create your online account.

@component('mail::button', ['url' => $url])
    Click here to confirm
@endcomponent

@endif

If you forget your username or password, please click here: [Forgot Password]({{ route('password.request') }})

@include('emails.partials.signature')
@endcomponent
