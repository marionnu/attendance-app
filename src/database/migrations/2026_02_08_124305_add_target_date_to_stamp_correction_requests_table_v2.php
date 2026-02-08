<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTargetDateToStampCorrectionRequestsTableV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
Schema::table('stamp_correction_requests', function (Blueprint $table) {
    $table->date('target_date')->after('attendance_id');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
Schema::table('stamp_correction_requests', function (Blueprint $table) {
    $table->dropColumn('target_date');
});
    }
}
