<?php
namespace App\Reports;

use App\Business;
use App\Caregiver;
use App\Traits\IsDirectoryReport;
use Illuminate\Support\Str;

class CaregiverDirectoryReport extends BusinessResourceReport
{
    use IsDirectoryReport;

    // Pagination
    private $per_page = 50;
    private $current_page = 1;
    private $total_count;
    private $sortBy;
    private $sortOrder;

    // Filters / Other
    private $alias_filter;
    private $active_filter;
    private $for_export;
    private $customFields = [];

    /**
     * @var bool
     */
    protected $generated = false;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rows;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * CaregiverDirectoryReport constructor.
     */
    public function __construct()
    {
        $this->query = Caregiver::with(['user', 'statusAlias', 'address', 'phoneNumber', 'user.emergencyContacts', 'user.phoneNumbers', 'businesses', 'referralSource'])
            ->leftJoin('users', 'caregivers.id', '=', 'users.id');
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
     * Filter by active status.
     *
     * @param bool|string|int $active
     * @return CaregiverDirectoryReport
     */
    public function setActiveFilter($active): self
    {
        $this->active_filter = $active;

        return $this;
    }

    /**
     * set for export flag
     *
     * @param bool $flag
     * @return CaregiverDirectoryReport
     */
    public function setForExport(bool $flag): self
    {
        $this->for_export = $flag;

        return $this;
    }

    /**
     * Filter by status alias.
     *
     * @param int|string $alias_id
     * @return CaregiverDirectoryReport
     */
    public function setStatusAliasFilter($alias_id): self
    {
        $this->alias_filter = $alias_id;

        return $this;
    }

    /**
     * Set the custom fields that should be returned.
     *
     * @param iterable $customFields
     * @return CaregiverDirectoryReport
     */
    public function setCustomFields(iterable $customFields): self
    {
        $this->customFields = $customFields;

        return $this;
    }

    /**
     * Set number of records to paginate per page
     *
     * @param int|string $count
     * @return CaregiverDirectoryReport
     */
    public function setPageCount($count): self
    {
        $this->per_page = $count;

        return $this;
    }

    /**
     * Set current page
     *
     * @param int|string $page
     * @return CaregiverDirectoryReport
     */
    public function setCurrentPage($page): self
    {
        $this->current_page = $page;

        return $this;
    }

    /**
     * Set sorting field and direction.
     *
     * @param string $sortBy
     * @param string $sortOrder
     * @return CaregiverDirectoryReport
     */
    public function setSort(string $sortBy, string $sortOrder) : self
    {
        $this->sortBy = $sortBy;
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     *
     * public accessor for the total count
     */
    public function getTotalCount()
    {
        return $this->total_count;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results(): ?iterable
    {
        switch ($this->active_filter) {
            case 'true':
                $this->query()->active();
                break;
            case 'false':
                $this->query()->inactive();
                break;
            default:
                break;
        }

        if ($this->alias_filter) {
            $this->query()->where('status_alias_id', $this->alias_filter);
        }

        if ($this->sortBy == 'lastname' || !$this->sortBy) {
            $this->query()->orderByRaw("users.lastname {$this->sortOrder}, users.firstname {$this->sortOrder}");
        } else if (in_array($this->sortBy, [
            'firstname', 'id', 'username', 'email', 'date_of_birth', 'gender', 'active', 'created_at', 'notification_phone',
        ])) {
            $this->query()->orderBy('users.'.$this->sortBy, $this->sortOrder);
        } else {
            $this->query()->orderBy('caregivers.'.$this->sortBy, $this->sortOrder);
        }

        // perform count-query first
        $this->total_count = $this->query()->with('meta')
            ->count();

        // implement pagination only when not exporting
        if (! $this->for_export) {
            $this->query()->limit($this->per_page)->offset($this->per_page * ($this->current_page - 1));
        }

        return $this->query()->get()->map(function (\App\Caregiver $caregiver) {
            $data = [
                'id' => $caregiver->id,
                'firstname' => $caregiver->firstname,
                'lastname' => $caregiver->lastname,
                'username' => Str::startsWith($caregiver->username, 'no_login_') ? null : $caregiver->username,
                'email' => Str::contains($caregiver->email, '@noemail.allyms.com') ? null : $caregiver->email,
                'title' => $caregiver->title,
                'date_of_birth' => $caregiver->date_of_birth,
                'certification' => $caregiver->certification,
                'gender' => $caregiver->gender,
                'active' => $caregiver->active,
                'status_alias' => optional($caregiver->statusAlias)->name,
                'office_location' => $caregiver->businesses->map(function (Business $item) {
                    return $item->name;
                })->implode(', '),
                'created_at' => $caregiver->created_at->toDateTimeString(),
                'address' => optional($caregiver->getAddress())->full_address,
                'phone' => optional($caregiver->getPhoneNumber())->number(),
                'notification_phone' => optional($caregiver->user)->notification_phone,
                'application_date' => optional($caregiver->application_date)->toDateTimeString(),
                'orientation_date' => optional($caregiver->orientation_date)->toDateTimeString(),
                'smoking_okay' => $caregiver->smoking_okay ? 'Yes' : 'No',
                'pets_dogs_okay' => $caregiver->pets_dogs_okay ? 'Yes' : 'No',
                'pets_cats_okay' => $caregiver->pets_cats_okay ? 'Yes' : 'No',
                'pets_birds_okay' => $caregiver->pets_birds_okay ? 'Yes' : 'No',
                'ethnicity' => ucfirst($caregiver->ethnicity),
                'medicaid_id' => $caregiver->medicaid_id,
                'emergency_contact' => optional($caregiver->user)->formatEmergencyContact(),
                'referral' => optional($caregiver->referralSource)->organization,
            ];

            $meta = [];
            foreach ($this->customFields as $field) {
                $meta[$field->key] = $this->mapMetaField($field, $caregiver->meta);
            }
            $data = array_merge($data, $meta);

            return $data;
        });
    }
}
