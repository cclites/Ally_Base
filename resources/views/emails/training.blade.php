@component('mail::message')
## Hello!

To get started please login to your account and review the FAQ and Tutorials available in our Knowledge Base.

@component('mail::button', ['url' => $url])
    View the Knowledge Base
@endcomponent

If you forget your username or password, please click here: [Forgot Password]({{ route('password.request') }})

@include('emails.partials.signature')
@endcomponent
