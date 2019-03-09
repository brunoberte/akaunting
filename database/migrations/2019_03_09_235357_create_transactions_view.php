<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement($this->dropView());
    }

    private function createView(): string
    {
        $prefix = \DB::getTablePrefix();
        return <<<SQL
CREATE VIEW `{$prefix}vw_transactions` AS
(select 'Payment' as type,
        t.`id`,
        t.`amount`,
        t.`currency_code`,
        t.`paid_at`,
        t.`description`,
        c.id      as category_id,
        c.name    as category_name,
        a.id      as account_id,
        a.name    as account_name,
        t.company_id,
        t.`deleted_at`
 from `0fc_payments` t
        left join `0fc_categories` c on c.id = t.category_id
        left join `0fc_accounts` a on a.id = t.account_id
 )
union all
(select 'Revenue' as type,
        t.`id`,
        t.`amount`,
        t.`currency_code`,
        t.`paid_at`,
        t.`description`,
        c.id      as category_id,
        c.name    as category_name,
        a.id      as account_id,
        a.name    as account_name,
        t.company_id,
        t.`deleted_at`
 from `0fc_revenues` t
        left join `0fc_categories` c on c.id = t.category_id
        left join `0fc_accounts` a on a.id = t.account_id
 )

SQL;
    }

    private function dropView(): string
    {
        $prefix = \DB::getTablePrefix();
        return <<<SQL
DROP VIEW IF EXISTS `{$prefix}vw_transactions`;
SQL;
    }
}
