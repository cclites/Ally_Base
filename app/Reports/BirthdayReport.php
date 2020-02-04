<?php


namespace App\Reports;

use App\Client;
use App\Caregiver;
use Carbon\Carbon;

class BirthdayReport extends BaseReport
{
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
     */
    public function __construct($type)
    {
        $this->type = strtolower($type);

        if ($this->type == 'clients') {
            $this->query = Client::forRequestedBusinesses()
                                 ->with(['user.addresses', 'user.phoneNumbers']);
        }
        else {
            $this->query = Caregiver::forRequestedBusinesses()
                                 ->with(['user.addresses', 'user.phoneNumbers']);
        }

    }


    public function includeContactInfo() {
        // Add city & phone when data retrieved.
        $this->formatters['add_contact_info'] = function($user) {
            $user->city = $user->user->addresses[0]->city ?? 'Unknown';
            $user->phone = $user->user->phoneNumbers[0]->national_number ?? 'Unknown';

            return $user;
        };
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }


    public function filterByClientId(int $client_id) : self {
        $this->query->where($this->type . '.id', $client_id);

        return $this;
    }

    public function filterByClientType(string $client_type) : self {
        $this->query->where('client_type', $client_type);

        return $this;
    }

    public function filterByDateRange(string $startDate, string $endDate) : self {
        // Dates must be formatted as follows: YYYY/MM/DD
        $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->format('Y/m/d');
        $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->format('Y/m/d');

        $this->query->select('users.date_of_birth','clients.*')->join('users', 'users.id', '=', 'clients.id');
        $this->query->whereRaw(
            'DATE_ADD(`users`.`date_of_birth`, INTERVAL YEAR(CURDATE()) - YEAR(`users`.`date_of_birth`) + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(`users`.`date_of_birth`), 1, 0) YEAR) BETWEEN "?" AND "?"',
            [$startDate, $endDate]
        );

        return $this;
    }

    /**
     * process the results
     *
     * @return Collection
     */
    protected function results() : iterable
    {
        $record =  $this->query()->get()->sortBy($this->type . '.name');

        return $record->values();
    }

}