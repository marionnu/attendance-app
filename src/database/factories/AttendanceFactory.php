<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-2 months', 'now');

        $clockIn = (clone $date)->setTime(
            $this->faker->numberBetween(9, 11),
            $this->faker->randomElement([0, 15, 30, 45])
        );

        $clockOut = (clone $date)->setTime(
            $this->faker->numberBetween(17, 21),
            $this->faker->randomElement([0, 15, 30, 45])
        );

        return [
            'user_id'    => 1,
            'work_date'  => $clockIn->format('Y-m-d'),
            'clock_in'   => $clockIn,
            'clock_out'  => $clockOut,
            'note'       => $this->faker->optional()->sentence(),
            'status'     => $this->faker->randomElement(['勤務外', '出勤中', '休憩中', '退勤済']),
        ];
    }
}
