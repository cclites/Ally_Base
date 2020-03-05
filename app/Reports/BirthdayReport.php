<?php


namespace App\Reports;

use App\Client;
use App\Caregiver;
use Carbon\Carbon;

class BirthdayReport extends BaseReport {
    /**
     * @var int
     */
    protected $client_id;

    /**
     * @var string
     */
    protected $type;

    /**
     * BusinessClientBirthdayReport constructor.
     *
     * @param string $type
     */
    public function __construct(string $type) {
        $this->type = strtolower($type);

        if ($this->type == 'clients') {
            $this->query = Client::forRequestedBusinesses()
                                 ->with(['user', 'addresses', 'phoneNumbers']);
        } else {
            $this->query = Caregiver::forRequestedBusinesses()
                                    ->with(['user', 'addresses', 'phoneNumbers']);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query() {
        return $this->query;
    }

    public function filterActiveOnly() {
        $this->query->whereHas('user', function ($q) {
            $q->where('active', 1);
        });
    }

    public function filterByClientId(int $client_id): self {
        $this->query->where($this->type . '.id', $client_id);

        return $this;
    }

    public function filterByClientType(string $client_type): self {
        $this->query->where('client_type', $client_type);

        return $this;
    }

    public function filterByDateRange(int $days): self {
        $this->query->select($this->type . '.*')->join('users', 'users.id', '=', $this->type . '.id');
        $this->query->whereRaw("DATE_ADD(users.date_of_birth, INTERVAL YEAR(CURDATE())-YEAR(users.date_of_birth) + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(users.date_of_birth),1,0) YEAR) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL $days DAY)");

        return $this;
    }

    /**
     * process the results
     *
     * @return iterable
     */
    protected function results(): iterable {
        return $this->query()->get()
                    ->sortBy($this->type . '.name')
                    ->map(function ($role) {
                        return [
                            'id'             => $role->id,
                            'name'           => $role->nameLastFirst,
                            'date_of_birth'  => $role->date_of_birth,
                            'street_address' => optional($role->address)->street_address,
                            'city'           => optional($role->address)->city ?? '-',
                            'state'          => optional($role->address)->state ?? '-',
                            'zip'            => optional($role->address)->zip ?? '-',
                            'phone'          => optional($role->phoneNumber)->number(),
                        ];
                    })
                    ->values();
    }
}