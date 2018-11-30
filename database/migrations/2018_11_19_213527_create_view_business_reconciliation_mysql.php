<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewBusinessReconciliationMysql extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::connection() instanceof \Illuminate\Database\MySqlConnection) {
            DB::connection()->statement('CREATE VIEW `view_business_reconciliation` AS (select `gateway_transactions`.`id` AS `id`,`deposits`.`amount` AS `amount_deposited`,\'0\' AS `amount_withdrawn`,`deposits`.`business_id` AS `business_id`,`gateway_transactions`.`created_at` AS `created_at` from (`deposits` join `gateway_transactions` on((`gateway_transactions`.`id` = `deposits`.`transaction_id`))) where ((`deposits`.`amount` >= 0) and isnull(`deposits`.`caregiver_id`))) union (select `gateway_transactions`.`id` AS `id`,\'0\' AS `amount_deposited`,`payments`.`amount` AS `amount_withdrawn`,`payments`.`business_id` AS `business_id`,`gateway_transactions`.`created_at` AS `created_at` from (`payments` join `gateway_transactions` on((`gateway_transactions`.`id` = `payments`.`transaction_id`))) where isnull(`payments`.`client_id`)) union (select `gateway_transactions`.`id` AS `id`,\'0\' AS `amount_deposited`,(`deposits`.`amount` * -(1)) AS `amount_withdrawn`,`deposits`.`business_id` AS `business_id`,`gateway_transactions`.`created_at` AS `created_at` from (`deposits` join `gateway_transactions` on((`gateway_transactions`.`id` = `deposits`.`transaction_id`))) where ((`deposits`.`amount` < 0) and isnull(`deposits`.`caregiver_id`)))');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::connection() instanceof \Illuminate\Database\MySqlConnection) {
            DB::connection()->statement('DROP VIEW `view_business_reconciliation`');
        }
    }
}
