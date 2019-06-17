<?php

namespace App\Reports;

use App\Caregiver;
use App\PhoneNumber;

class CaregiverContactInfoReport extends BaseReport
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->query = Caregiver::query();
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        return $this->query()
            ->get()
            ->map(function (Caregiver $item) {
                return [
                    'ID' => $item->id,
                    'Name' => $item->nameLastFirst,
                    'Email' => $item->user->email,
                    'Phone Numbers' => $item->user->phoneNumbers->map(function (PhoneNumber $item) {
                        return $item->number;
                    })->implode(', '),
                ];
            })
            ->sortBy('Name');
    }
}
