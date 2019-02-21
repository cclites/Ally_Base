<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateViewBusinessReconciliationMysql extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::connection() instanceof \Illuminate\Database\MySqlConnection) {
            DB::connection()->statement('DROP VIEW IF EXISTS `view_business_reconciliation`');
            DB::connection()->statement('CREATE VIEW `view_business_reconciliation` AS 
SELECT transaction_id, business_id, id as deposit_id, null as payment_id, amount as amount_deposited, 0 as amount_withdrawn, success, created_at FROM deposits WHERE caregiver_id IS NULL AND amount >= 0
UNION
SELECT transaction_id, business_id, id as deposit_id, null as payment_id, 0 as amount_deposited, amount * -1 as amount_withdrawn, success, created_at FROM deposits WHERE caregiver_id IS NULL AND amount < 0
UNION
SELECT transaction_id, payment_method_id as business_id, null as deposit_id, id as payment_id, 0 as amount_deposited, amount as amount_withdrawn, success, created_at FROM payments WHERE payment_method_type = "businesses" AND amount >= 0
UNION
SELECT transaction_id, payment_method_id as business_id, null as deposit_id, id as payment_id, amount * -1 as amount_deposited, 0 as amount_withdrawn, success, created_at FROM payments WHERE payment_method_type = "businesses" AND amount < 0;');
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
            DB::connection()->statement('DROP VIEW IF EXISTS `view_business_reconciliation`');
            DB::connection()->statement('CREATE VIEW `view_business_reconciliation` AS (select `gateway_transactions`.`id` AS `id`,`deposits`.`amount` AS `amount_deposited`,\'0\' AS `amount_withdrawn`,`deposits`.`business_id` AS `business_id`,`gateway_transactions`.`created_at` AS `created_at` from (`deposits` join `gateway_transactions` on((`gateway_transactions`.`id` = `deposits`.`transaction_id`))) where ((`deposits`.`amount` >= 0) and isnull(`deposits`.`caregiver_id`))) union (select `gateway_transactions`.`id` AS `id`,\'0\' AS `amount_deposited`,`payments`.`amount` AS `amount_withdrawn`,`payments`.`business_id` AS `business_id`,`gateway_transactions`.`created_at` AS `created_at` from (`payments` join `gateway_transactions` on((`gateway_transactions`.`id` = `payments`.`transaction_id`))) where isnull(`payments`.`client_id`)) union (select `gateway_transactions`.`id` AS `id`,\'0\' AS `amount_deposited`,(`deposits`.`amount` * -(1)) AS `amount_withdrawn`,`deposits`.`business_id` AS `business_id`,`gateway_transactions`.`created_at` AS `created_at` from (`deposits` join `gateway_transactions` on((`gateway_transactions`.`id` = `deposits`.`transaction_id`))) where ((`deposits`.`amount` < 0) and isnull(`deposits`.`caregiver_id`)))');
        }
    }
}
