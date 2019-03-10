<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ModifyDateColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dateTime('paid_at')->change();
        });

        Schema::table('revenues', function (Blueprint $table) {
            $table->dateTime('paid_at')->change();
        });

        Schema::table('recurring', function (Blueprint $table) {
            $table->dateTime('started_at')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
