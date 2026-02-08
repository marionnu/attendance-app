<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => '管理者',
            'email' => 'admin@test.com',
        ]);

        $staffUsers = User::factory()->count(10)->create();

        foreach ($staffUsers as $user) {

            $dates = collect(range(0, 59))
                ->map(fn ($i) => Carbon::today()->subDays($i)->toDateString())
                ->shuffle()
                ->take(20);

            $attendances = collect();

            foreach ($dates as $d) {
                $attendances->push(
                    Attendance::factory()->create([
                        'user_id'   => $user->id,
                        'work_date' => $d,
                    ])
                );
            }

            foreach ($attendances as $attendance) {
                $breakCount = rand(1, 2);

                for ($i = 0; $i < $breakCount; $i++) {
                    BreakTime::factory()->create([
                        'attendance_id' => $attendance->id,
                    ]);
                }
            }
        }
    }
}
