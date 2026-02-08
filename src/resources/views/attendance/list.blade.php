@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="container">
  <h2 class="page-title">勤怠一覧</h2>

  <div class="month-nav">
    <a class="month-nav__btn" href="{{ route('attendance.list', ['month' => $prevMonth]) }}">← 前月</a>
    <div class="month-nav__label">
      {{ $base->format('Y/m') }}
    </div>
    <a class="month-nav__btn" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">翌月 →</a>
  </div>

  <div class="table-wrap">
    <table class="attendance-table">
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
        @forelse ($attendances as $a)
          @php
            $clockIn  = $a->clock_in ? \Carbon\Carbon::parse($a->clock_in) : null;
            $clockOut = $a->clock_out ? \Carbon\Carbon::parse($a->clock_out) : null;

            $breakMinutes = 0;
            foreach ($a->breaks ?? [] as $b) {
              if ($b->break_in && $b->break_out) {
                $breakMinutes += \Carbon\Carbon::parse($b->break_in)->diffInMinutes(\Carbon\Carbon::parse($b->break_out));
              }
            }

            $workMinutes = 0;
            if ($clockIn && $clockOut) {
              $workMinutes = $clockIn->diffInMinutes($clockOut) - $breakMinutes;
              if ($workMinutes < 0) $workMinutes = 0;
            }

            $fmtHM = fn($m) => sprintf('%d:%02d', intdiv($m, 60), $m % 60);
          @endphp

          <tr>
            <td>{{ \Carbon\Carbon::parse($a->work_date)->format('m/d(D)') }}</td>
            <td>{{ $clockIn ? $clockIn->format('H:i') : '' }}</td>
            <td>{{ $clockOut ? $clockOut->format('H:i') : '' }}</td>
            <td>{{ $breakMinutes ? $fmtHM($breakMinutes) : '' }}</td>
            <td>{{ $workMinutes ? $fmtHM($workMinutes) : '' }}</td>
            <td>
              <a href="{{ route('attendance.detail', $a->id) }}">詳細</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align:center; padding:16px;">勤怠データがありません</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
