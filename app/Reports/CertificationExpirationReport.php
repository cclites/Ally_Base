<?php

namespace App\Reports;

use App\CaregiverLicense;
use App\Contracts\BusinessReportInterface;
use App\User;
use Carbon\Carbon;

class CertificationExpirationReport extends BaseReport implements BusinessReportInterface
{
    protected $caregiverId;
    protected $activeOnly = false;
    protected $inactiveOnly = false;
    protected $name;
    protected $showExpired;
    protected $days;

    public function setCaregiver(?int $id) : self
    {
        $this->caregiverId = $id;
        return $this;
    }

    public function setActiveOnly(?bool $activeOnly) : self
    {
        $this->activeOnly = $activeOnly;
        return $this;
    }

    public function setInactiveOnly(?bool $inactiveOnly) : self
    {
        $this->inactiveOnly = $inactiveOnly;
        return $this;
    }

    public function setName(?string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    public function setExpired(?bool $showExpired) : self
    {
        $this->showExpired = $showExpired;
        return $this;
    }

    public function setDays(?int $days) : self
    {
        $this->days = $days;
        return $this;
    }

    /**
     * ScheduledPaymentsReport constructor.
     */
    public function __construct()
    {
        $this->query = CaregiverLicense::with('caregiver');
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
    protected function results()
    {
        $query = $this->query()->whereHas('caregiver', function ($q) {
            if ($this->activeOnly) {
                $q->active();
            } else if ($this->inactiveOnly) {
                $q->inactive();
            }
        });

        if ($this->caregiverId) {
            $query->where('caregiver_id', $this->caregiverId);
        }

        if (isset($this->name)) {
            $query->where('name', 'LIKE', "%{$this->name}%");
        }

        if ($this->showExpired) {
            $query->whereBetween('expires_at', [
                Carbon::now()->subYears(10)->format('Y-m-d'),
                Carbon::now()->format('Y-m-d'),
            ]);
        } else {
            $query->whereBetween('expires_at', [
                Carbon::now()->format('Y-m-d'),
                Carbon::today()->addDays($this->days)->format('Y-m-d'),
            ]);
        }

        return $query->get()->map(function (CaregiverLicense $license) {
            return [
                'id' => $license->id,
                'name' => $license->name,
                'expiration_date' => (new Carbon($license->expires_at))->format('Y-m-d'),
                'caregiver_id' => $license->caregiver->id,
                'caregiver_name' => $license->caregiver->nameLastFirst(),
                'caregiver_active' => $license->caregiver->active,
            ];
        });
    }

    public function forBusinesses(array $businessIds = null)
    {
        $this->query()->whereHas('caregiver', function($q) use ($businessIds) {
            $q->forBusinesses($businessIds);
        });

        return $this;
    }

    public function forRequestedBusinesses(array $businessIds = null, User $authorizedUser = null)
    {
        $this->query()->whereHas('caregiver', function($q) use ($businessIds, $authorizedUser) {
            $q->forRequestedBusinesses($businessIds, $authorizedUser);
        });

        return $this;
    }
}