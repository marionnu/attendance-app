<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBreakNoToBreakTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('break_times', function (Blueprint $table) {
        $table->unsignedTinyInteger('break_no')->default(1)->after('attendance_id');
    });
}

public function down()
{
    Schema::table('break_times', function (Blueprint $table) {
        $table->dropColumn('break_no');
    });
}

}
