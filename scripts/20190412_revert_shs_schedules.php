<?php
require __DIR__ . "/bootstrap.php";

$groups = \App\Schedule::where('business_id', 74)
    ->where('starts_at', '>', '2023-01-01 00:00:00')
    ->groupBy('group_id')
    ->pluck('group_id');
$schedules = \App\Schedule::where('business_id', 74)
    ->where(function ($q) use ($groups) {
        $q->whereIn('group_id', $groups)
            ->orWhere('updated_at', '>=', '2019-04-12 19:10:00');
    })

    ->get();
foreach($schedules as $schedule) {
    $audit = $schedule->audits()->latest()->first();
    $oldStartsAt = $audit->old_values['starts_at'] ?? null;
    if ($oldStartsAt) {
        $schedule->update(['starts_at' => $oldStartsAt]);
    }
}