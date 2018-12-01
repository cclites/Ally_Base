<?php
namespace App\Reports;

use App\Client;

class ClientDirectoryReport extends BusinessResourceReport
{
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
     * @var array
     */
    protected $columns;

    /**
     * ClientDirectoryReport constructor.
     */
    public function __construct()
    {
        $this->query = Client::with(['user', 'address']);
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
     * Count the number of rows
     *
     * @return int
     */
    public function count()
    {
        if ($this->rows) return $this->rows->count();
        return $this->query()->count();
    }

    /**
     * Save which columns to remove out of the final generated report
     *
     * @param array $params
     * @return void
     */
    public function applyColumnFilters($params)
    {
        foreach($params as $column => $shouldBePresent) {
            if(!$shouldBePresent) {
                $this->columns[] = $column;
            }
        }
    }

    /**
     * Remove columns that were set to be removed out of the final generated repor
     *
     * @param \Illuminate\Support\Collection $rows
     * @return \Illuminate\Support\Collection
     */
    protected function filterColumns($rows)
    {
        return $rows->map(function($client) {
            foreach ($this->columns as $column) {
                if(isset($client[$column])) {
                    unset($client[$column]);
                }
            }

            return $client;
        });
    }


    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $clients = $this->query->get();
        $this->generated = true;
        $rows = $clients->map(function(Client $client) {
            return [
                'id' => $client->id,
                'firstname' => $client->user->firstname,
                'lastname' => $client->user->lastname,
                'email' => $client->user->email,
                'active' => $client->active ? 'Active' : 'Inactive',
                'address' => $client->address ? $client->address->full_address : '',
                'date_added' => $client->user->created_at->format('m-d-Y'),
            ];
        });

        $rows = $this->filterColumns($rows);
        return $rows;
    }
}
