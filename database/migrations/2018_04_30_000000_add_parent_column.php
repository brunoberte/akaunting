<?php

use Illuminate\Database\Migrations\Migration;

class AddParentColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('revenues', function ($table) {
            $table->integer('parent_id')->default(0);
        });

        Schema::table('payments', function ($table) {
            $table->integer('parent_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('revenues', function ($table) {
            $table->dropColumn('parent_id');
        });

        Schema::table('payments', function ($table) {
            $table->dropColumn('parent_id');
        });
    }
}
