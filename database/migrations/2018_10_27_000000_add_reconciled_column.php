<?php

use Illuminate\Database\Migrations\Migration;

class AddReconciledColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function ($table) {
            $table->boolean('reconciled')->default(0);
        });

        Schema::table('revenues', function ($table) {
            $table->boolean('reconciled')->default(0);
        });
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('payments', function ($table) {
			$table->dropColumn('reconciled');
		});

        Schema::table('revenues', function ($table) {
            $table->dropColumn('reconciled');
        });
	}
}
