<?php

namespace App\Console\Commands;

use App\Caregiver;
use App\Client;
use App\Shift;
use Illuminate\Console\Command;

class Caregiver1099Csv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:caregiver_1099 {year?} {--output=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate caregiver 1099 data';

    /**
     * @var string
     */
    protected $csvSeparator = ',';

    /**
     * @var int|float
     */
    protected $minimumThreshold = 650;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rows = [];
        $year = (int) $this->argument('year') ?? date('Y');

        // Disable full group by mode
        \DB::statement('set session sql_mode=\'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\';');

        // Get rows
        $rows = \DB::select("SELECT c.id as client_id, CONCAT(u1.firstname, ' ', u1.lastname) as client_name, u1.email as client_email, c.client_type, c.default_payment_type, c.ssn as client_ssn, 
a1.address1 as client_address1, a1.address2 as client_address2, a1.city as client_city, a1.state as client_state, a1.zip as client_zip,
u2.id as caregiver_id, CONCAT(u2.firstname, ' ', u2.lastname) as caregiver_name, u2.email as caregiver_email, c2.ssn as caregiver_ssn,
a2.address1 as caregiver_address1, a2.address2 as caregiver_address2, a2.city as caregiver_city, a2.state as caregiver_state, a2.zip as caregiver_zip,
 sum(h.caregiver_total) as payment_total
FROM clients c
INNER JOIN shifts s ON s.client_id = c.id
INNER JOIN payments p ON s.payment_id = p.id
INNER JOIN shift_cost_history h ON h.id = s.id
INNER JOIN users u1 ON u1.id = s.client_id
INNER JOIN users u2 ON u2.id = s.caregiver_id
INNER JOIN caregivers c2 ON c2.id = u2.id
LEFT JOIN addresses a1 ON a1.id = (SELECT id FROM addresses WHERE user_id = u1.id ORDER BY `type` LIMIT 1)
LEFT JOIN addresses a2 ON a2.id = (SELECT id FROM addresses WHERE user_id = u2.id ORDER BY `type` LIMIT 1)
WHERE p.created_at BETWEEN '{$year}-01-01 00:00:00' AND '{$year}-12-31 23:59:59'
GROUP BY s.client_id, s.caregiver_id
HAVING payment_total > ?", [$this->minimumThreshold]);

        $csv = implode($this->csvSeparator, array_keys((array) $rows[0])) . "\n";
        foreach($rows as $row) {
            $row = (array) $row;
            $row['client_ssn'] = $row['client_ssn'] ? \Crypt::decrypt($row['client_ssn']) : null;
            $row['caregiver_ssn'] = $row['caregiver_ssn'] ? \Crypt::decrypt($row['caregiver_ssn']) : null;
            $csv .= implode($this->csvSeparator, $row) . "\n";
        }

        if ($this->option('output')) {
            return file_put_contents($this->option('output'), $csv);
        }
        echo "\n";
        echo $csv;

    }

}
