<?php
namespace App\Reports;

use App\Client;
use App\Traits\IsDirectoryReport;

class ClientDirectoryReport extends BusinessResourceReport
{
    use IsDirectoryReport;

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
        $this->query = Client::with(['user', 'address']);
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
