<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleRequest extends Model
{
    //
}


/*
okay so the problem is this: I need to hash out the model relatiomnship for shcedule requests...

the icon with grab requests:
- for schedules that are still open
- for open schedules that dont have an accepted status amongst their group

these are two relationships I need to create..

for now, the count can simply be on the table..
*/