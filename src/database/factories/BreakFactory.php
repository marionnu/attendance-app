<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BreakFactory extends Factory
{
    public function definition(): array
    {
        $breakStartHour = $this->faker->numberBetween(12, 15);
        $breakStartMin  = $this->faker->randomElement([0, 15, 30, 45]);

        $breakStart = now()->setTime($breakStartHour, $breakStartMin);

        $breakEnd = (clone $breakStart)->addMinutes($this->faker->numberBetween(30, 60));

        return [
            'attendance_id' => 1,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ];
    }
}
