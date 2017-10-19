Hello.  If this is {{ $schedule->caregiver->user->firstname }} clocking-out for
{{ $schedule->client->name() }}'s care, please press 1.

If you are trying to clock in,  Press 2.

If you continue to receive this message with incorrect information, please call
the {{ $schedule->client->business->name }} office at
{{ $schedule->client->business->phone1 }}. Thank you.
