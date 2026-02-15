<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTargetDateToStampCorrectionRequestsTableV2 extends Migration
{
    public function up()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('stamp_correction_requests', 'target_date')) {
                $table->date('target_date')->after('attendance_id');
            }
        });
    }

    public function down()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            if (Schema::hasColumn('stamp_correction_requests', 'target_date')) {
                $table->dropColumn('target_date');
            }
        });
    }
}
