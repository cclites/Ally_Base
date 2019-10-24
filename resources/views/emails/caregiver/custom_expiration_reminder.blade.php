@component('mail::message')

@php
    $greeting = str_replace('#caregiver-name#', $caregiver->user->firstname, $template->greeting);
    $body = $template->body;
    $body = str_replace('#expiring-item-name#', $license->name, $body);
    $body = str_replace('#expiring-item-date#', $license->expires_at->format('m/d/Y'), $body);
    $body = str_replace('#registry-name#', $business->name, $body);



@endphp
    {{ $greeting }},
    {{ $body }}
@endcomponent
