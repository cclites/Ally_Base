<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Arr;

class BusinessSettingsResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (is_office_user() || is_admin()) {
            return array_merge($this->resource->toArray(), [
                'hha_password' => $this->hha_password ? '********' : '',
                'tellus_password' => $this->tellus_password ? '********' : '',
            ]);
        }

        $settings = [
            'id',
            'name',
            'type',
            // 'default_commission_rate',
            'timezone',
            'scheduling',
            'mileage_rate',
            'calendar_default_view',
            'calendar_caregiver_filter',
            'calendar_remember_filters',
            'auto_confirm',
            'ask_on_confirm',
            'allows_manual_shifts',
            'location_exceptions',
            'timesheet_exceptions',
            'require_signatures',
            'co_mileage',
            'co_injuries',
            'co_comments',
            'co_expenses',
            'co_issues',
            'co_signature',
            'co_caregiver_signature',
            'calendar_next_day_threshold',
            'multi_location_registry',
            'shift_rounding_method',
            'pay_cycle',
            'last_day_of_cycle',
            'last_day_of_first_period',
            'mileage_reimbursement_rate',
            'unpaired_pay_rates',
            'overtime_hours_day',
            'overtime_hours_week',
            'overtime_consecutive_days',
            'dbl_overtime_hours_day',
            'dbl_overtime_consecutive_days',
            'overtime_method',
            'allow_client_confirmations',
            'auto_confirm_modified',
            'shift_confirmation_email',
            'sce_shifts_in_progress',
            'charge_diff_email',
            'auto_append_hours',
            'auto_confirm_unmodified_shifts',
            'auto_confirm_verified_shifts',
            // 'rate_structure',
            // 'include_ally_fee',
            'use_rate_codes',
            'chain_id',
            'ot_multiplier',
            'ot_behavior',
            'hol_multiplier',
            'hol_behavior',
        ];

        return Arr::only($this->resource->toArray(), $settings);
    }
}
