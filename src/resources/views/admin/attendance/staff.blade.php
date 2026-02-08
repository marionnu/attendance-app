@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin_staff_attendance.css') }}">
@endsection

@section('content')
@php
  use Carbon\Carbon;

  $titleMonth = $base->format('Y年n月');
  $daysInMonth = $base->daysInMonth;

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

<div class="container admin-staff-attendance">
  <div class="card">

    <h2 class="page-title">{{ $user->name }}さんの勤怠</h2>

    <div class="month-nav">
      <a class="nav-btn" href="{{ route('admin.attendance.staff.index', ['user' => $user->id, 'month' => $prevMonth]) }}">← 前月</a>

      <div class="month-center">
        <span class="calendar-icon">📅</span>
        <span class="month-text">{{ $base->format('Y/m') }}</span>
      </div>

      <a class="nav-btn" href="{{ route('admin.attendance.staff.index', ['user' => $user->id, 'month' => $nextMonth]) }}">翌月 →</a>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @for($d = 1; $d <= $daysInMonth; $d++)
            @php
              $dateStr = $base->copy()->day($d)->toDateString(); // Y-m-d
              $a = $attendances->get($dateStr);

              $breakMin = $a ? $sumBreakMinutes($a) : 0;
              $workMin  = $a ? $sumWorkMinutes($a) : null;

              $breakText = ($a && $breakMin > 0) ? $toHourMinute($breakMin) : '';
              $workText  = ($a && !is_null($workMin)) ? $toHourMinute($workMin) : '';
            @endphp

            <tr>
              <td>{{ Carbon::parse($dateStr)->format('m/d(D)') }}</td>
              <td>{{ $a ? $fmt($a->clock_in) : '' }}</td>
              <td>{{ $a ? $fmt($a->clock_out) : '' }}</td>
              <td>{{ $breakText }}</td>
              <td>{{ $workText }}</td>
              <td>
                @if($a)
                  <a href="{{ route('admin.attendance.show', ['attendance' => $a->id, 'date' => $dateStr]) }}">詳細</a>
                @else
                  <span>-</span>
                @endif
              </td>
            </tr>
          @endfor
        </tbody>
      </table>
    </div>

    <div class="csv-area">
  <a class="csv-btn"
     href="{{ route('admin.attendance.staff.export', ['user' => $user->id, 'month' => $base->format('Y-m')]) }}">
    CSV出力
  </a>
</div>

  </div>
</div>
@endsection
