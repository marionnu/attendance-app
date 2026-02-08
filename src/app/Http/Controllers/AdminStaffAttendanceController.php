<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminStaffAttendanceController extends Controller
{

    public function index(Request $request, User $user)
    {
        $month = $request->query('month');
        $base = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : Carbon::today()->startOfMonth();

        $start = $base->copy()->startOfMonth()->toDateString();
        $end   = $base->copy()->endOfMonth()->toDateString();
        $attendances = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->with('breaks')
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->work_date)->toDateString());
        $prevMonth = $base->copy()->subMonth()->format('Y-m');
        $nextMonth = $base->copy()->addMonth()->format('Y-m');

        return view('admin.attendance.staff', compact(
            'user', 'base', 'prevMonth', 'nextMonth', 'attendances'
        ));
    }

    public function export(Request $request, User $user)
    {
        $month = $request->query('month');
        $base = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : Carbon::today()->startOfMonth();
        $start = $base->copy()->startOfMonth()->toDateString();
        $end   = $base->copy()->endOfMonth()->toDateString();
        $attendances = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->with('breaks')
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->work_date)->toDateString());
        $sumBreakMinutes = function ($attendance) {
            $minutes = 0;
            foreach (($attendance->breaks ?? collect()) as $b) {
                if ($b->break_in && $b->break_out) {
                    $in  = Carbon::parse($b->break_in);
                    $out = Carbon::parse($b->break_out);
                    if ($out->gte($in)) {
                        $minutes += $in->diffInMinutes($out);
                    }
                }
            }
            return $minutes;
        };

        $toHourMinute = function (int $minutes) {
            $h = intdiv($minutes, 60);
            $m = $minutes % 60;
            return sprintf('%d:%02d', $h, $m);
        };
        $sumWorkMinutes = function ($attendance) use ($sumBreakMinutes) {
            if (!$attendance->clock_in || !$attendance->clock_out) return null;

            $in  = Carbon::parse($attendance->clock_in);
            $out = Carbon::parse($attendance->clock_out);
            if ($out->lt($in)) return null;

            $total = $in->diffInMinutes($out);
            $total -= $sumBreakMinutes($attendance);
            return max($total, 0);
        };

        $fmt = fn($t) => $t ? Carbon::parse($t)->format('H:i') : '';
        $safeName = preg_replace('/[\\\\\/:*?"<>|]/', '_', $user->name);
        $filename = $safeName . '_' . $base->format('Ym') . '_attendance.csv';

        return response()->streamDownload(function () use ($base, $attendances, $sumBreakMinutes, $sumWorkMinutes, $toHourMinute, $fmt) {

            $out = fopen('php://output', 'w');
            $header = ['日付', '出勤', '退勤', '休憩', '合計'];
            fputs($out, mb_convert_encoding(implode(',', $header) . "\r\n", 'SJIS-win', 'UTF-8'));
            $daysInMonth = $base->daysInMonth;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = $base->copy()->day($d)->toDateString();
                $a = $attendances->get($dateStr);

                if ($a) {
                    $breakMin = $sumBreakMinutes($a);
                    $workMin  = $sumWorkMinutes($a);

                    $row = [
                        Carbon::parse($dateStr)->format('Y/m/d'),
                        $fmt($a->clock_in),
                        $fmt($a->clock_out),
                        $breakMin > 0 ? $toHourMinute($breakMin) : '',
                        is_null($workMin) ? '' : $toHourMinute($workMin),
                    ];
                } else {
                    $row = [
                        Carbon::parse($dateStr)->format('Y/m/d'),
                        '', '', '', '',
                    ];
                }
                $escaped = array_map(function ($v) {
                    $v = (string) $v;
                    $v = str_replace('"', '""', $v);
                    return '"' . $v . '"';
                }, $row);

                fputs($out, mb_convert_encoding(implode(',', $escaped) . "\r\n", 'SJIS-win', 'UTF-8'));
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=SJIS-win',
        ]);
    }
}
