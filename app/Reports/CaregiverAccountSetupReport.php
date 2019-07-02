<?php

namespace App\Reports;

use App\Caregiver;
use App\PhoneNumber;

class CaregiverAccountSetupReport extends BaseReport
{
    private $phoneFilter;
    private $statusFilter;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->query = Caregiver::with('user', 'user.phoneNumbers');
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
     * Filter by account status.
     *
     * @param $status
     * @return CaregiverAccountSetupReport
     */
    public function setStatusFilter($status) : self
    {
        $this->statusFilter = $status;

        return $this;
    }

    /**
     * Filter by phone type.
     *
     * @param $phone
     * @return CaregiverAccountSetupReport
     */
    public function setPhoneFilter($phone) : self
    {
        $this->phoneFilter = $phone;

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        switch ($this->statusFilter) {
            case 'scheduled':
                $this->query()->active()->whereHasShiftsOrSchedules()->whereNotSetup();
                break;
            case 'no_bank':
                $this->query()->active()->whereHasShiftsOrSchedules()->whereDoesntHave('bankAccount');
                break;
            default:
                $this->query()->active()->whereNotSetup();
                break;
        }

        $data = $this->query()
            ->get()
            ->map(function (Caregiver $item) {
                if (empty($item->user->setup_status)) {
                    $status = 'Not Started';
                } else if (in_array($item->user->setup_status, [Caregiver::SETUP_CREATED_ACCOUNT, Caregiver::SETUP_CONFIRMED_PROFILE])) {
                    $status = 'In Progress';
                } else if ($item->user->setup_status == Caregiver::SETUP_ADDED_PAYMENT) {
                    $status = 'Complete';
                }

                return [
                    'id' => $item->id,
                    'name' => $item->nameLastFirst,
                    'email' => $item->user->email,
                    'mobile_phone' => optional($item->user->phoneNumbers->where('receives_sms', 1)->first())->number ?? '',
                    'home_phone' => optional($item->user->phoneNumbers->where('receives_sms', 0)->first())->number ?? '',
                    'setup_status' => $status,
                ];
            })
            ->sortBy('name')
            ->values();

        switch ($this->phoneFilter) {
            case 'has_mobile':
                $data = $data->filter(function ($row) {
                    return filled($row['mobile_phone']);
                });
                break;
            case 'any':
                $data = $data->filter(function ($row) {
                    return filled($row['mobile_phone']) || filled($row['home_phone']);
                });
                break;
            case 'none':
                $data = $data->filter(function ($row) {
                    return empty($row['mobile_phone']) && empty($row['home_phone']);
                });
                break;
        }

        return $data->values();
    }
}
