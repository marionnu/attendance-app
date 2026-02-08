<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            $table->date('target_date')->after('attendance_id');
        });
    }

    public function down(): void
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            $table->dropColumn('target_date');
        });
    }
};
