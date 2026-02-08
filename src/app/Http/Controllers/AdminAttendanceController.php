<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Http\Requests\Admin\UpdateAdminAttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))->toDateString()
            : now()->toDateString();

        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('work_date', $date)
            ->orderBy('user_id')
            ->get();

        $prevDate = Carbon::parse($date)->subDay()->toDateString();
        $nextDate = Carbon::parse($date)->addDay()->toDateString();

        return view('admin.attendance.list', compact('date', 'prevDate', 'nextDate', 'attendances'));
    }

    public function show(Request $request, Attendance $attendance)
    {
        $attendance->load(['user', 'breaks', 'correctionRequests']);

        $date = $request->query('date', Carbon::parse($attendance->work_date)->toDateString());

        $hasPending = $attendance->correctionRequests()
            ->where('status', 'pending')
            ->exists();

        return view('admin.attendance.detail', [
            'attendance' => $attendance,
            'hasPending' => $hasPending,
            'date' => $date,
        ]);
    }

    public function update(AdminAttendanceUpdateRequest $request, \App\Models\Attendance $attendance)
    {
        $validated = $request->validated();

        $attendance->update([
            'clock_in'  => $validated['clock_in'],
            'clock_out' => $validated['clock_out'],
            'note'      => $validated['note'],
        ]);

    $breaks = $attendance->breaks()->orderBy('id')->get();

    return redirect()
        ->route('admin.attendance.show', $attendance)
        ->with('success', '勤怠を修正しました');
}

    private function upsertBreak(Attendance $attendance, int $index, ?string $in, ?string $out): void
    {
        $break = $attendance->breaks()->orderBy('id')->skip($index)->first();

        if (empty($in) && empty($out)) {
            if ($break) {
                $break->delete();
            }
            return;
        }

        if (!$break) {
            $break = new BreakTime();
            $break->attendance_id = $attendance->id;
        }

        $break->break_in  = $in;
        $break->break_out = $out;
        $break->save();
    }
}
