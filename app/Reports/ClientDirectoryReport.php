<?php
namespace App\Reports;

use App\Client;
use App\Traits\IsDirectoryReport;
use App\CustomField;
use App\User;

class ClientDirectoryReport extends BusinessResourceReport
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
    private $client_type;
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
     * ClientDirectoryReport constructor.
     */
    public function __construct()
    {
        $this->query = Client::with(['user', 'address', 'user.phoneNumbers', 'business'])
            ->leftJoin('users', 'clients.id', '=', 'users.id');
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
     * Filter by client type.
     *
     * @param $type
     * @return ClientDirectoryReport
     */
    public function setClientTypeFilter($type): self
    {
        $this->client_type = $type;

        return $this;
    }

    /**
     * Filter by client type.
     *
     * @param $flag
     * @return ClientDirectoryReport
     */
    public function setForExport($flag): self
    {
        $this->for_export = $flag;

        return $this;
    }

    /**
     * Filter by status alias.
     *
     * @param $alias_id
     * @return ClientDirectoryReport
     */
    public function setStatusAliasFilter($alias_id): self
    {
        $this->alias_filter = $alias_id;

        return $this;
    }

    /**
     * Filter by active status.
     *
     * @param $active
     * @return ClientDirectoryReport
     */
    public function setActiveFilter($active): self
    {
        $this->active_filter = $active;

        return $this;
    }

    /**
     * Set the custom fields that should be returned.
     *
     * @param iterable $customFields
     * @return ClientDirectoryReport
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
     * @return ClientDirectoryReport
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
     * @return ClientDirectoryReport
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
     * @return ClientDirectoryReport
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
    protected function results()
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

        if ($this->client_type) {
            $this->query()->where('client_type', $this->client_type);
        }

        if ($this->alias_filter) {
            $this->query()->where('status_alias_id', $this->alias_filter);
        }

        if ($this->sortBy == 'lastname' || !$this->sortBy) {
            $this->query()->orderByRaw("users.lastname {$this->sortOrder}, users.firstname {$this->sortOrder}");
        } else if (in_array($this->sortBy, [
            'firstname', 'id', 'username', 'email', 'date_of_birth', 'gender', 'active', 'created_at', 'updated_at',
        ])) {
            $this->query()->orderBy('users.'.$this->sortBy, $this->sortOrder);
        } else {
            $this->query()->orderBy('clients.'.$this->sortBy, $this->sortOrder);
        }

        // perform count-query first
        $this->total_count = $this->query()->with('meta')
            ->count();

        // implement pagination manually
        if (! $this->for_export) {
            $this->query()->limit($this->per_page)->offset($this->per_page * ($this->current_page - 1));
        }

        $this->generated = true;
        return $this->query()->get()->map(function (Client $client) {
            $data = [
                'id' => $client->id,
                'firstname' => $client->firstname,
                'lastname' => $client->lastname,
                'username' => starts_with($client->username, 'no_login_') ? null : $client->username,
                'date_of_birth' => $client->date_of_birth,
                'gender' => $client->gender,
                'email' => str_contains($client->email, '@noemail.allyms.com') ? null : $client->email,
                'active' => $client->active,
                'office_location' => $client->business->name,
                'address' => optional($client->getAddress())->full_address,
                'phone' => optional($client->getPhoneNumber())->number(),
                'client_type' => str_replace('_', ' ', ucwords($client->client_type)),
                'status_alias' => optional($client->statusAlias)->name,

                'created_at' => $client->created_at->toDateTimeString(),
                'created_by' => optional($client->creator)->name,
                'updated_at' => optional($client->updated_at)->toDateTimeString(),
                'updated_by' => optional($client->updator)->name,

                'services_coordinator' => optional($client->caseManager)->name,
                'salesperson' => optional($client->salesperson)->fullName(),
                'inquiry_date' => optional($client->inquiry_date)->toDateTimeString(),
                'service_start_date' => optional($client->service_start_date)->toDateTimeString(),
                'referral' => optional($client->referralSource)->organization,
                'ambulatory' => $client->ambulatory ? 'Yes' : 'No',
                'caregiver_1099' => $client->caregiver_1099,
                'agreement_status' => str_replace('_', ' ', ucwords($client->agreement_status)),
                'hic' => $client->hic,
                'diagnosis' => $this->for_export ? $client->diagnosis : str_limit($client->diagnosis, 15),
            ];

            $meta = [];
            foreach ($this->customFields as $field) {
                $meta[$field->key] = $this->mapMetaField($field, $client->meta);
            }
            $data = array_merge($data, $meta);

            return $data;
        });
    }
}