<?php
namespace App\Reports;

use App\Caregiver;
use App\CustomField;
use App\Traits\IsDirectoryReport;
use Illuminate\Support\Collection;

class CaregiverDirectoryReport extends BusinessResourceReport
{
    use IsDirectoryReport;

    // Pagination
    private $per_page = 100;
    private $current_page = 1;
    private $total_count;
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
        $this->query = Caregiver::with(['user', 'address', 'user.emergencyContacts', 'user.phoneNumbers']);
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

        // perform count-query first
        $this->total_count = $this->query()->with('meta')
            ->count();

        // implement pagination only when not exporting
        if (! $this->for_export) {
            $this->query()->limit($this->per_page)->offset($this->per_page * ($this->current_page - 1));
        }

        $caregivers = $this->query()->get();

        return $caregivers->map(function (\App\Caregiver $caregiver) {
            $data = [
                'id' => $caregiver->id,
                'firstname' => $caregiver->firstname,
                'lastname' => $caregiver->lastname,
                'username' => starts_with($caregiver->username, 'no_login_') ? null : $caregiver->username,
                'title' => $caregiver->title,
                'date_of_birth' => $caregiver->date_of_birth,
                'certification' => $caregiver->certification,
                'gender' => $caregiver->gender,
                'orientation_date' => optional($caregiver->orientation_date)->toDateTimeString(),
                'smoking_okay' => $caregiver->smoking_okay ? 'Yes' : 'No',
                'pets_dogs_okay' => $caregiver->pets_dogs_okay ? 'Yes' : 'No',
                'pets_cats_okay' => $caregiver->pets_cats_okay ? 'Yes' : 'No',
                'pets_birds_okay' => $caregiver->pets_birds_okay ? 'Yes' : 'No',
                'ethnicity' => ucfirst($caregiver->ethnicity),
                'application_date' => optional($caregiver->application_date)->toDateTimeString(),
            'status_alias' => optional($caregiver->statusAlias)->name,
                'medicaid_id' => $caregiver->medicaid_id,
                'email' => str_contains($caregiver->email, '@noemail.allyms.com') ? null : $caregiver->email,
            'notification_phone' => optional($caregiver->user)->notification_phone,
                'active' => $caregiver->active,
            'address' => optional($caregiver->getAddress())->full_address,
            'phone' => optional($caregiver->getPhoneNumber())->number(),
            'emergency_contact' => optional($caregiver->user)->formatEmergencyContact(),
                'created_at' => $caregiver->created_at->toDateTimeString(),
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

    /**
     * Get Caregiver's meta value for custom field.
     *
     * @param CustomField $field
     * @param Collection|null $caregiverMeta
     * @return string|null
     */
    private function mapMetaField(CustomField $field, ?Collection $caregiverMeta)
    {
        if (empty($caregiverMeta)) {
            return null;
        }

        if ($meta = $caregiverMeta->where('key', $field->key)->first()) {
            $value = $meta->display();

            // trim longer values for the table
            if (! $this->for_export) {
                if (strlen($value) > 25 && in_array($field->type, ['input', 'textarea'])) {
                    return substr($value, 0, 25) . '...';
                }
            }

            return $value;
        }

        return null;
    }
}
