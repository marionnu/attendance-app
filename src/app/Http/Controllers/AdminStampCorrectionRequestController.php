<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        if (!in_array($status, ['pending', 'approved'], true)) {
            $status = 'pending';
        }

        $requests = StampCorrectionRequest::with('user')
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.stamp_correction_requests.index', compact('requests', 'status'));
    }

    public function show(\App\Models\StampCorrectionRequest $stampCorrectionRequest)
    {
        $stampCorrectionRequest->load(['user', 'attendance']);
        $user = $stampCorrectionRequest->user;
        $attendance = $stampCorrectionRequest->attendance;
        $breaks = $attendance ? $attendance->breaks()->orderBy('id')->get() : collect();
        $break1 = $breaks->get(0);
        $break2 = $breaks->get(1);

        return view('admin.stamp_correction_requests.show', compact(
            'stampCorrectionRequest',
            'user',
            'attendance',
            'break1',
            'break2'
        ));
    }

    public function approve(Request $request, StampCorrectionRequest $stampCorrectionRequest)
    {
        if ($stampCorrectionRequest->status !== 'pending') {
            return back()->withErrors(['approve' => 'この申請は承認待ちではありません。']);
        }

        DB::transaction(function () use ($stampCorrectionRequest) {

            $attendance = $stampCorrectionRequest->attendance()->lockForUpdate()->firstOrFail();

            // ✅ baseDate は必ず「日付だけ」に正規化（ここがポイント）
            $baseDateRaw =
                $attendance->work_date
                ?? $stampCorrectionRequest->target_date
                ?? now()->toDateString();

            $baseDate = Carbon::parse($baseDateRaw)->format('Y-m-d');

            // ✅ time を安全に datetime に変換（YYYY-mm-dd 00:00:00 12:17:00 を作らない）
            $toDateTime = function (?string $time) use ($baseDate) {
                if (empty($time)) return null;

                // すでに日付入り（例: 2026-02-08 12:17:00 / 2026-02-08T12:17:00）ならそのまま
                if (preg_match('/^\d{4}-\d{2}-\d{2}/', $time)) {
                    return Carbon::parse($time)->format('Y-m-d H:i:s');
                }

                // timeだけ（12:17 / 12:17:00）なら秒を補完して結合
                $t = strlen($time) === 5 ? $time . ':00' : $time;

                return Carbon::createFromFormat('Y-m-d H:i:s', "{$baseDate} {$t}")->format('Y-m-d H:i:s');
            };

            $attendance->clock_in  = $toDateTime($stampCorrectionRequest->requested_clock_in);
            $attendance->clock_out = $toDateTime($stampCorrectionRequest->requested_clock_out);

            if (isset($attendance->note)) {
                $attendance->note = $stampCorrectionRequest->note ?? $stampCorrectionRequest->reason ?? null;
            }

            $attendance->save();

            $this->upsertBreak(
                $attendance,
                0,
                $toDateTime($stampCorrectionRequest->requested_break1_in),
                $toDateTime($stampCorrectionRequest->requested_break1_out)
            );

            $this->upsertBreak(
                $attendance,
                1,
                $toDateTime($stampCorrectionRequest->requested_break2_in),
                $toDateTime($stampCorrectionRequest->requested_break2_out)
            );

            $stampCorrectionRequest->status = 'approved';
            $stampCorrectionRequest->save();
        });

        return redirect()
            ->route('admin.stamp_correction_requests.show', [
                'stampCorrectionRequest' => $stampCorrectionRequest->id,
                'tab' => 'pending',
            ])
            ->with('status', '承認しました。');
    }

    private function upsertBreak($attendance, int $index, ?string $in, ?string $out): void
    {
        $break = $attendance->breaks()->orderBy('id')->skip($index)->first();

        if (empty($in) && empty($out)) {
            if ($break) $break->delete();
            return;
        }

        if (!$break) {
            $break = $attendance->breaks()->make();
        }

        $break->break_in  = $in;
        $break->break_out = $out;
        $break->save();
    }
}
