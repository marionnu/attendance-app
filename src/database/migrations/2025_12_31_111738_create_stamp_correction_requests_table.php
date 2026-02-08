<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();

            $table->string('status')->default('pending')->index();

            $table->time('requested_clock_in')->nullable();
            $table->time('requested_clock_out')->nullable();

            $table->time('requested_break1_in')->nullable();
            $table->time('requested_break1_out')->nullable();

            $table->time('requested_break2_in')->nullable();
            $table->time('requested_break2_out')->nullable();

            $table->text('note');

            $table->timestamps();

            $table->index(['attendance_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stamp_correction_requests');
    }
};
