<?php
namespace App\Reports;

use App\Client;
use App\Traits\IsDirectoryReport;
use App\CustomField;

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
        $customFields = CustomField::forAuthorizedChain()->where('user_type', 'client')->get();
        $rows = $clients->map(function(Client $client) use(&$customFields) {
            $result = [
                'id' => $client->id,
                'first_name' => $client->user->firstname,
                'last_name' => $client->user->lastname,
                'email' => $client->user->email,
                'active' => $client->active ? 'Active' : 'Inactive',
                'address' => $client->address ? $client->address->full_address : '',
                'date_added' => $client->user->created_at->format('m-d-Y'),
            ];

            // Add the custom fields to the report row
            foreach($customFields as $field) {
                if($meta = $client->meta->where('key', $field->key)->first()) {
                    $result[$field->key] = $meta->display();
                    continue;
                }

                $result[$field->key] = $field->default();
            }

            return $result;
        });

        $rows = $this->filterColumns($rows);
        return $rows;
    }
}
