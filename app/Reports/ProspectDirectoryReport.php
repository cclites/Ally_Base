<?php
namespace App\Reports;

use App\Prospect;
use App\Traits\IsDirectoryReport;

class ProspectDirectoryReport extends BusinessResourceReport
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
     * ProspectDirectoryReport constructor.
     */
    public function __construct()
    {
        $this->query = Prospect::query();
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $prospects = $this->query->get();
        $this->generated = true;
        $rows = $prospects->map(function(Prospect $prospect) {
            return [
                'id' => $prospect->id,
                'firstname' => $prospect->firstname,
                'lastname' => $prospect->lastname,
                'email' => $prospect->email,
                'address' => $prospect->full_address,
                'date_added' => $prospect->created_at->format('m-d-Y'),
            ];
        });

        $rows = $this->filterColumns($rows);
        return $rows;
    }
}
