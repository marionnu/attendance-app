<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->with('breaks')
            ->first();

        return view('attendance.index', compact('attendance'));
    }

    public function list(Request $request)
    {
        $user = auth()->user();

        $month = $request->query('month');
        $base = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : Carbon::today()->startOfMonth();

        $start = $base->copy()->startOfMonth()->toDateString();
        $end   = $base->copy()->endOfMonth()->toDateString();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->with('breaks')
            ->orderBy('work_date')
            ->get();

        $prevMonth = $base->copy()->subMonth()->format('Y-m');
        $nextMonth = $base->copy()->addMonth()->format('Y-m');

        return view('attendance.list', compact('attendances', 'base', 'prevMonth', 'nextMonth'));
    }

    public function clockIn()
    {
        $user = auth()->user();
        $now = Carbon::now();
        $today = $now->toDateString();

        DB::transaction(function () use ($user, $now, $today) {
            $attendance = Attendance::firstOrCreate(
                ['user_id' => $user->id, 'work_date' => $today],
                ['status' => 'off']
            );

            if ($attendance->status !== 'off') {
                abort(403, '本日はすでに出勤しています');
            }

            $attendance->update([
                'clock_in' => $now,
                'status'   => 'working',
            ]);
        });

        return redirect()->route('attendance.index')->with('status', '出勤しました。');
    }

    public function breakStart()
    {
        $user = auth()->user();
        $now = Carbon::now();
        $today = $now->toDateString();

        DB::transaction(function () use ($user, $now, $today) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('work_date', $today)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attendance->status !== 'working') {
                abort(403, '休憩に入れません');
            }

            $openBreakExists = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_out')
                ->exists();
            if ($openBreakExists) {
                abort(403, 'すでに休憩中です');
            }

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_in' => $now,
            ]);

            $attendance->update(['status' => 'break']);
        });

        return redirect()->route('attendance.index')->with('status', '休憩に入りました。');
    }

    public function breakEnd()
    {
        $user = auth()->user();
        $now = Carbon::now();
        $today = $now->toDateString();

        DB::transaction(function () use ($user, $now, $today) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('work_date', $today)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attendance->status !== 'break') {
                abort(403, '休憩戻できません');
            }

            $break = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_out')
                ->latest('id')
                ->firstOrFail();

            $break->update(['break_out' => $now]);
            $attendance->update(['status' => 'working']);
        });

        return redirect()->route('attendance.index')->with('status', '休憩から戻りました。');
    }

    public function clockOut()
    {
        $user = auth()->user();
        $now = Carbon::now();
        $today = $now->toDateString();

        DB::transaction(function () use ($user, $now, $today) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('work_date', $today)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attendance->status !== 'working') {
                abort(403, '退勤できません');
            }

            $openBreak = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_out')
                ->exists();
            if ($openBreak) {
                abort(403, '休憩中は退勤できません');
            }

            $attendance->update([
                'clock_out' => $now,
                'status' => 'finished',
            ]);
        });

        return redirect()->route('attendance.index')->with('status', 'お疲れ様でした。');
    }

    public function requestList(Request $request)
    {
        $user = auth()->user();

        $tab = $request->query('tab', 'pending');
        if (!in_array($tab, ['pending', 'approved'], true)) {
            $tab = 'pending';
        }

        $requests = StampCorrectionRequest::where('user_id', $user->id)
            ->where('status', $tab)
            ->orderByDesc('created_at')
            ->get();

        return view('stamp_correction_request.list', compact('requests', 'tab'));
    }

    public function detail(Attendance $attendance)
{
    if ($attendance->user_id !== auth()->id()) {
        abort(403);
    }

    $pendingRequest = \App\Models\StampCorrectionRequest::where('attendance_id', $attendance->id)
        ->where('status', 'pending')
        ->latest()
        ->first();

    return view('attendance.detail', [
        'attendance' => $attendance,
        'pendingRequest' => $pendingRequest,
    ]);
}

}
