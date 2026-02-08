<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStampCorrectionRequest;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $tab = $request->query('tab', 'pending');
        if (!in_array($tab, ['pending', 'approved'], true)) {
            $tab = 'pending';
        }

        $requests = StampCorrectionRequest::where('user_id', $userId)
            ->where('status', $tab)
            ->with(['attendance', 'user'])
            ->latest()
            ->get();

        return view('stamp_correction_request.list', compact('requests', 'tab'));
    }

    public function store(StoreStampCorrectionRequest $request, Attendance $attendance)
{
    if ($attendance->user_id !== auth()->id()) {
        abort(403);
    }

    $hasPending = StampCorrectionRequest::where('attendance_id', $attendance->id)
        ->where('status', 'pending')
        ->exists();

    if ($hasPending) {
        return back()
            ->withErrors(['clock' => '承認待ちのため修正はできません。'])
            ->withInput();
    }

    $data = $request->validated();

    $targetDate =
        $attendance->date
        ?? $attendance->work_date
        ?? $attendance->attended_date
        ?? $attendance->target_date
        ?? $attendance->created_at?->toDateString()
        ?? now()->toDateString();

    StampCorrectionRequest::create([
    'user_id'       => auth()->id(),
    'attendance_id' => $attendance->id,
    'target_date'   => $targetDate,
    'status'        => 'pending',

    'requested_clock_in'   => $data['clock_in'],
    'requested_clock_out'  => $data['clock_out'],

    'requested_break1_in'  => $data['break1_in'] ?? null,
    'requested_break1_out' => $data['break1_out'] ?? null,

    'requested_break2_in'  => $data['break2_in'] ?? null,
    'requested_break2_out' => $data['break2_out'] ?? null,
    'note' => $request->input('note') ?? $request->input('reason'),
]);

    return redirect()
    ->route('stamp_correction_request.list', ['tab' => 'pending'])
    ->with('status', '申請しました。');
}

}
