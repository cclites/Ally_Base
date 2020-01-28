<?php

use App\Scheduling\RuleParser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateAndMigrateScheduleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::disableForeignKeyConstraints();

        // Delete schedule_id foreign key(s)
        Schema::table('shifts', function (Blueprint $table) {
            if (\DB::getDriverName() != 'sqlite') {
                $table->dropForeign('shifts_schedule_id_foreign');
            }
            $table->dropColumn('schedule_id');
        });
        if (\DB::getDriverName() != 'sqlite') {
            Schema::table('schedule_activities', function (Blueprint $table) {
                $table->dropForeign('fk_schedule_activities_schedule_id');
            });
        }
        DB::statement('DELETE FROM schedule_activities WHERE 1'); // delete all schedule activities (not used at this time)

        // Create new schedules table
        Schema::rename('schedules', 'schedules_old');
        Schema::create('schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('caregiver_id')->nullable();
            $table->tinyInteger('weekday');
            $table->dateTime('starts_at');
            $table->unsignedSmallInteger('duration');
            $table->unsignedSmallInteger('overtime_duration')->default(0);
            $table->unsignedInteger('note_id')->nullable();
            $table->decimal('caregiver_rate', 8, 2)->default(0);
            $table->decimal('provider_fee', 8, 2)->default(0);
            $table->string('hours_type', 45)->default('default');
            $table->timestamps();
            $table->softDeletes();

            $table->index('starts_at');
            $table->index('weekday');
            $table->foreign('business_id')->references('id')->on('businesses')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('caregiver_id')->references('id')->on('caregivers')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        // Re-add schedule_id foreigns
        Schema::table('shifts', function (Blueprint $table) {
            $table->unsignedInteger('schedule_id')->nullable()->after('business_id');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('restrict')->onUpdate('cascade');
        });
        Schema::table('schedule_activities', function (Blueprint $table) {
            $table->foreign('schedule_id')->references('id')->on('schedules')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        // Create notes table
        Schema::create('schedule_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->text('note');
            $table->timestamps();
        });


        // Migrate existing schedules to new format
        $schedules = DB::select('SELECT * FROM schedules_old WHERE end_date >= start_date');
        foreach($schedules as $schedule) {
            // Load Business (for timezone)
            $business = \App\Business::find($schedule->business_id);
            if (!$business) continue;

            // Create note
            $note = null;
            if ($schedule->notes) {
                $note = \App\ScheduleNote::create(['note' => $schedule->notes]);
            }

            if (!$schedule->rrule) {
                // Load occurrences array with single event
                $occurrences = [new Carbon($schedule->start_date . ' ' . $schedule->time, $business->timezone)];
            }
            else {
                $start = new Carbon('2017-09-01', $business->timezone);
                $end = Carbon::now($business->timezone)->addYears(2);
                if ($end->format('Y-m-d') > $schedule->end_date) {
                    $end = new Carbon($schedule->end_date . ' ' . $schedule->time, $business->timezone);
                }
                if ($start->format('Y-m-d') < $schedule->start_date) {
                    $start = new Carbon($schedule->start_date . ' ' . $schedule->time, $business->timezone);
                }
                $occurrences = RuleParser::create($start, $schedule->rrule)
                                      ->getOccurrencesBetween($start, $end, 400);
            }

            foreach($occurrences as $occurrence) {
                // Check for a schedule exception
                if (
                    DB::table('schedule_exceptions')
                      ->where('schedule_id', $schedule->id)
                      ->where('date', $occurrence->format('Y-m-d'))
                      ->exists()
                ) {
                    continue;
                }

                // Create schedule entry for the specific date
                $entry = \App\Schedule::create([
                    'business_id' => $schedule->business_id,
                    'caregiver_id' => $schedule->caregiver_id,
                    'client_id' => $schedule->client_id,
                    'starts_at' => $occurrence->format('Y-m-d H:i:s'),
                    'duration' => $schedule->duration,
                    'overtime_duration' => (in_array($schedule->hours_type, ['overtime', 'holiday'])) ? $schedule->duration : 0,
                    'weekday' => $occurrence->format('w'),
                    'note_id' => $note->id ?? null,
                    'caregiver_rate' => $schedule->caregiver_rate ?? 0,
                    'provider_fee' => $schedule->provider_fee ?? 0,
                    'hours_type' => $schedule->hours_type ?? 'default',
                ]);

                // Update shifts corresponding to the old schedule
                \App\Shift::whereNull('schedule_id')
                    ->where('business_id', $schedule->business_id)
                    ->where('client_id', $schedule->client_id)
                    ->where('caregiver_id', $schedule->caregiver_id)
                    ->whereBetween('checked_in_time', [
                            Carbon::instance($occurrence)->copy()->setTimezone('UTC')->subHours(3),
                            Carbon::instance($occurrence)->copy()->setTimezone('UTC')->addHours(3),
                        ])
                    ->update(['schedule_id' => $entry->id]);
            }
        }

        // Drop exceptions table
        Schema::dropIfExists('schedule_exceptions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('schedule_notes');
        Schema::rename('schedules_old', 'schedules');
        Schema::create('schedule_exceptions', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('schedule_id')->unsigned()->index('fk_schedule_exceptions_schedule_id_idx');
            $table->date('date');
            $table->timestamps();
        });
    }
}
