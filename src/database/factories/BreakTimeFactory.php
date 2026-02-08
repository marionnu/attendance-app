<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition(): array
    {
        $attendance = Attendance::inRandomOrder()->first();

        $clockIn  = $attendance?->clock_in ? Carbon::parse($attendance->clock_in) : Carbon::today()->setTime(9, 0);
        $clockOut = $attendance?->clock_out ? Carbon::parse($attendance->clock_out) : Carbon::today()->setTime(18, 0);

        $breakIn = (clone $clockIn)->addMinutes($this->faker->numberBetween(60, 240));

        $breakOut = (clone $breakIn)->addMinutes($this->faker->randomElement([15, 30, 45, 60]));
        if ($breakOut->greaterThan($clockOut)) {
            $breakOut = (clone $clockOut)->subMinutes(5);
        }

        return [
            'attendance_id' => $attendance?->id ?? 1,
            'break_in'      => $breakIn,
            'break_out'     => $breakOut,
        ];
    }
}
