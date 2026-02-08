<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestedColumnsToStampCorrectionRequestsTable extends Migration
{
    public function up(): void
{
    Schema::table('stamp_correction_requests', function (Blueprint $table) {

        if (!Schema::hasColumn('stamp_correction_requests', 'requested_clock_in')) {
            $table->time('requested_clock_in')->nullable();
        }
        if (!Schema::hasColumn('stamp_correction_requests', 'requested_clock_out')) {
            $table->time('requested_clock_out')->nullable();
        }
        if (!Schema::hasColumn('stamp_correction_requests', 'requested_break1_in')) {
            $table->time('requested_break1_in')->nullable();
        }
        if (!Schema::hasColumn('stamp_correction_requests', 'requested_break1_out')) {
            $table->time('requested_break1_out')->nullable();
        }
        if (!Schema::hasColumn('stamp_correction_requests', 'requested_break2_in')) {
            $table->time('requested_break2_in')->nullable();
        }
        if (!Schema::hasColumn('stamp_correction_requests', 'requested_break2_out')) {
            $table->time('requested_break2_out')->nullable();
        }
        if (!Schema::hasColumn('stamp_correction_requests', 'note')) {
            $table->text('note');
        }
    });
}

public function down(): void
{
    Schema::table('stamp_correction_requests', function (Blueprint $table) {
        $table->dropColumn([
            'status',
            'requested_clock_in',
            'requested_clock_out',
            'requested_break1_in',
            'requested_break1_out',
            'requested_break2_in',
            'requested_break2_out',
            'note',
        ]);
    });
}

}
