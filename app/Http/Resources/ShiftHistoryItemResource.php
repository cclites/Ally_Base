<?php

namespace App\Http\Resources;

use App\Billing\Invoiceable\ShiftService;
use App\Shift;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Str;

class ShiftHistoryItemResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Shift $shift */
        $shift = $this->resource;

        $hourlyRates = $shift->costs()->getHourlyRates();
        $totalRates = $shift->costs()->getTotalRates();

        if( filled( $shift->schedule ) && $shift->checked_out_time ){

            $time_difference = number_format( ( float )( ( $shift->schedule->duration / 60 ) - $shift->duration() ), 2, '.', '' );

            $scheduled_start_time = $shift->schedule->getStartDateTime()->format( 'h A' );
            $scheduled_end_time   = $shift->schedule->getEndDateTime()->format( 'h A' );
            $scheduled_time_difference = "$scheduled_start_time - $scheduled_end_time ( $time_difference )";

        } else $scheduled_time_difference = null;

        return [
            'id' => $shift->id,
            'checked_in_time' => $shift->checked_in_time->format('c'),
            'checked_out_time' => optional($shift->checked_out_time)->format('c'),
            'hours' => $shift->duration(),
            'client_id' => $shift->client_id,
            'business_id' => $shift->business_id,
            'client_name' => optional($shift->client)->nameLastFirst(),
            'caregiver_id' => $shift->caregiver_id,
            'caregiver_name' => optional($shift->caregiver)->nameLastFirst(),
            'fixed_rates' => $shift->fixed_rates,
            'caregiver_rate' => number_format($hourlyRates->caregiver_rate, 2),
            'provider_fee' => number_format($hourlyRates->provider_fee, 2),
            'ally_fee' => number_format($hourlyRates->ally_fee, 2),
            'hourly_total' => number_format($hourlyRates->client_rate, 2),
            'other_expenses' => number_format($shift->other_expenses, 2),
            'mileage' => number_format($shift->mileage, 2),
            'mileage_costs' => number_format($shift->costs()->getMileageCost(), 2),
            'caregiver_total' => number_format($totalRates->caregiver_rate, 2),
            'provider_total' => number_format($totalRates->provider_fee, 2),
            'ally_total' => number_format($totalRates->ally_fee, 2),
            'ally_pct' => $shift->getAllyPercentage(),
            'shift_total' => number_format($totalRates->client_rate, 2),
            'hours_type' => $shift->hours_type,
            'confirmed' => $shift->statusManager()->isConfirmed(),
            'confirmed_at' => $shift->confirmed_at,
            'client_confirmed' => $shift->client_confirmed,
            // Invoiced = if it ever was waiting for a charge
            // This is faulty if the invoice is ever un-invoiced, but accepting this flaw.
            'invoiced' => $shift->wasInvoiced(),
            'charged' => !($shift->statusManager()->isPending()),
            'charged_at' => $shift->charged_at,
            'status' => $shift->status ? title_case(preg_replace('/_/', ' ', $shift->status)) : '',
            // Send both verified and EVV for backwards compatibility
            'verified' => $shift->verified,
            'EVV' => ($shift->checked_in_verified && $shift->checked_out_verified),
            'flags' => $shift->flags,
            'created_at' => optional($shift->created_at)->toDateTimeString(),
            'services' => $this->mapServices($shift),

            'admin_note' => ( is_admin() || is_office_user() ) ? $shift->admin_note : null,

            'visit_edit_reason_id' => optional( $shift->visitEditReason )->formatted_name,
            'visit_edit_action_id' => optional( $shift->visitEditAction )->formatted_name,

            'scheduled_time_difference' => $scheduled_time_difference,
        ];
    }

    /**
     * Map shift services into string values.
     *
     * @param Shift $shift
     * @return iterable|null
     */
    private function mapServices(Shift $shift) : ?iterable
    {
        if ($shift->service) {
            return [$shift->service->code . '-' . Str::limit($shift->service->name, 8) . '(' . $shift->getRawDuration() . ')'];
        } else if ($shift->services->count()) {
            return $shift->services->map(function (ShiftService $shiftService) {
                return $shiftService->service->code . '-' . Str::limit($shiftService->service->name, 8) . '(' . $shiftService->duration . ')';
            });
        }

        return null;
    }
}
