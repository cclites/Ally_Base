<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

/**
 * Class FilteredResourceRequest
 * @package App\Http\Requests
 */
class FilteredResourceRequest extends FormRequest
{
    /**
     * A default authorize() method to return true.  Authorization rules normally belong in a policy, called from the controller.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * This request only validates if json or export is present.
     *
     * @return void
     */
    public function validate()
    {
        if (! $this->forJson() && ! $this->forExport()) {
            return;
        }

        parent::validate();
    }

    /**
     * Detect if request wants JSON data.
     *
     * @return bool
     */
    public function forJson() : bool
    {
        return $this->expectsJson() && $this->query->get('json') == '1';
    }

    /**
     * Detect if request is for an export.
     *
     * @return bool
     */
    public function forExport() : bool
    {
        return $this->query->get('export') == '1';
    }

    /**
     * Determine if the request is asking for data or export.
     *
     * @return bool
     */
    public function wantsReportData(): bool
    {
        return $this->forExport() || $this->forJson();
    }

    /**
     * Filter the start and end dates from one timezone into another
     * and return a tuple of Carbon instances.
     *
     * @param string $startKey
     * @param string $endKey
     * @param string|null $fromTimezone
     * @param string $toTimezone
     * @return array
     */
    public function filterDateRange(string $startKey = 'start_date', string $endKey = 'end_date', ?string $fromTimezone = null, string $toTimezone = 'UTC') : array
    {
        if (empty($fromTimezone)) {
            $fromTimezone = auth()->user()->getTimezone();
        }

        return [
            (new Carbon($this->validated()[$startKey] . ' 00:00:00', $fromTimezone))->setTimezone($toTimezone),
            (new Carbon($this->validated()[$endKey] . ' 23:59:59', $fromTimezone))->setTimezone($toTimezone)
        ];
    }
}
