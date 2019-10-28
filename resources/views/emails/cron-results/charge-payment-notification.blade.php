@component('mail::message')

## Results from Charge/Payment Notifications CRON

**Total emails sent:** {{ $total }}

**Emails sent to Clients:** {{ $clients->count() }}

**Emails sent to Caregivers:** {{ $clients->count() }}

@if (! $errors->isEmpty())
**Errors:**
@foreach ($errors as $error)
* {{ $error }}
@endforeach
@endif


@if (! $clients->isEmpty())
**Clients:**
@component('mail::table')
| ID | Name | Email |
| ------------- | ------------- | ------------- |
@foreach ($clients as $client)
| {{ $client['id'] }} | {{ $client['name'] }} | {{ $client['email'] }} |
@endforeach
@endcomponent
@endif

@if (! $caregivers->isEmpty())
**Caregivers:**
@component('mail::table')
| ID | Name | Email |
| ------------- | ------------- | ------------- |
@foreach ($caregivers as $caregiver)
| {{ $caregiver['id'] }} | {{ $caregiver['name'] }} | {{ $caregiver['email'] }} |
@endforeach
@endcomponent
@endif

**Raw Output:**
@foreach ($log as $message)
* {{ $message }}
@endforeach

@endcomponent
