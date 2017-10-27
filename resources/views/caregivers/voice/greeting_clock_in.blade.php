Hello.  If this is {{ $schedule->caregiver->user->firstname }} clocking-in for
{{ $schedule->client->name() }}'s care, please press 1.

If this information is not correct, please make sure the number you are calling
from is a valid form of electronic visit verification on the client's file.

If you continue to receive this message with incorrect information, please call
the {{ $schedule->client->business->name }} office at
{{ $schedule->client->business->phone1 }}. Thank you. At the end of the visit,
please remember to call again to clock-out.
