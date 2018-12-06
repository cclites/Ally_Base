@component('mail::message')
Hello {{ $caregiver->user->firstname }},

This is a friendly reminder that, according to our records, your {{ $license->name }} certification expires on {{ $license->expires_at->format('m/d/Y') }}.
Please contact {{ $business->name }} with your updated certification information as soon as possible.

Thank you!

@include('emails.partials.signature')
@endcomponent
