@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')
@php
  use Carbon\Carbon;

  $titleDate = Carbon::parse($date)->format('Y年n月j日');

  $fmt = fn($t) => $t ? Carbon::parse($t)->format('H:i') : '';

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
@endphp

<div class="container admin-attendance">

  <div class="card">
    <h3 class="sub-title">{{ $titleDate }}の勤怠</h3>

    <div class="date-nav">
      <a class="nav-btn" href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}">← 前日</a>

      <div class="date-center">
        <span class="calendar-icon">📅</span>
        <span class="date-text">{{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}</span>
      </div>

      <a class="nav-btn" href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">翌日 →</a>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
          </tr>
        </thead>

        <tbody>
          @forelse($attendances as $a)
            @php
              $breakMin = $sumBreakMinutes($a);
              $workMin  = $sumWorkMinutes($a);

              $breakText = $breakMin > 0 ? $toHourMinute($breakMin) : '';
              $workText  = is_null($workMin) ? '' : $toHourMinute($workMin);
            @endphp

            <tr>
              <td>{{ $a->user->name ?? '' }}</td>
              <td>{{ $fmt($a->clock_in) }}</td>
              <td>{{ $fmt($a->clock_out) }}</td>
              <td>{{ $breakText }}</td>
              <td>{{ $workText }}</td>
              <td>
                <a href="{{ route('admin.attendance.show', ['attendance' => $a->id, 'date' => $date]) }}">
                  詳細
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="empty">この日の勤怠データがありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
