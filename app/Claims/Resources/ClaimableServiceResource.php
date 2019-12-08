<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Carbon\Carbon;

class ClaimableServiceResource extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Claims\ClaimableService
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $timezone = optional(auth()->user()->officeUser)->getTimezone();
        if (empty($timezone)) {
            $timezone = 'America/New_York';
        }

        return [
            'shift_id' => $this->resource->shift_id,

            'address1' => $this->resource->address1,
            'address2' => $this->resource->address2,
            'city' => $this->resource->city,
            'state' => $this->resource->state,
            'zip' => $this->resource->zip,
            'latitude' => $this->resource->latitude,
            'longitude' => $this->resource->longitude,

            'evv_start_time' => optional($this->resource->evv_start_time)->toDateTimeString(),
            'evv_end_time' => optional($this->resource->evv_end_time)->toDateTimeString(),

            'checked_in_number' => $this->resource->checked_in_number,
            'checked_out_number' => $this->resource->checked_out_number,
            'checked_in_latitude' => $this->resource->checked_in_latitude,
            'checked_in_longitude' => $this->resource->checked_in_longitude,
            'checked_out_latitude' => $this->resource->checked_out_latitude,
            'checked_out_longitude' => $this->resource->checked_out_longitude,
            'has_evv' => $this->resource->has_evv,
            'evv_method_in' => $this->resource->evv_method_in,
            'evv_method_out' => $this->resource->evv_method_out,
            'service_id' => $this->resource->service_id,
            'service_name' => $this->resource->service_name,
            'service_code' => $this->resource->service_code,
            'activities' => $this->resource->activities,
            'caregiver_comments' => $this->resource->caregiver_comments,

            'shift_start_date' => $this->resource->scheduled_start_time->setTimezone($timezone)->format('m/d/Y'),
            'shift_end_date' => $this->resource->scheduled_end_time->setTimezone($timezone)->format('m/d/Y'),
            'shift_start_time' => $this->resource->scheduled_start_time->setTimezone($timezone)->format('H:i'),
            'shift_end_time' => $this->resource->scheduled_end_time->setTimezone($timezone)->format('H:i'),
            'service_start_date' => $this->resource->visit_start_time->setTimezone($timezone)->format('m/d/Y'),
            'service_start_time' => $this->resource->visit_start_time->setTimezone($timezone)->format('H:i'),

            // New service items added 12/2019:
            'client_signature_id' => $this->resource->client_signature_id,
            'caregiver_signature_id' => $this->resource->caregiver_signature_id,
            'is_overtime' => $this->resource->is_overtime,
        ];
    }
}
