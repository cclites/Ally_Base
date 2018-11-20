<?php
namespace App\Http\Requests;


class TimesheetReportRequest extends BusinessRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'client_id' => 'nullable|int',
            'caregiver_id' => 'nullable|int',
            'client_type' => 'nullable|string',
            'export_type' => 'required|string',
        ];
    }
}
