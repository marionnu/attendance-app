@extends('layouts.admin')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
@php
  $fmt = function ($v) {
    if (empty($v)) return '--:--';
    try {
      return \Carbon\Carbon::parse($v)->format('H:i');
    } catch (\Exception $e) {
      return '--:--';
    }
  };

  $req = $stampCorrectionRequest ?? $req ?? null;

  $rawDate =
      $req?->target_date
      ?? $attendance->work_date
      ?? $attendance->date
      ?? $attendance->worked_on
      ?? $attendance->attendance_date
      ?? $attendance->created_at;

  $dateStr = $rawDate ? \Carbon\Carbon::parse($rawDate)->format('Y年n月j日') : '';

  $isPending = ($req?->status ?? '') === 'pending';

  $clockIn  = $isPending ? ($req?->requested_clock_in  ?? null) : ($attendance->clock_in  ?? null);
  $clockOut = $isPending ? ($req?->requested_clock_out ?? null) : ($attendance->clock_out ?? null);

  $b1In  = $isPending ? ($req?->requested_break1_in  ?? null) : ($break1->break_in  ?? null);
  $b1Out = $isPending ? ($req?->requested_break1_out ?? null) : ($break1->break_out ?? null);

  $b2In  = $isPending ? ($req?->requested_break2_in  ?? null) : ($break2->break_in  ?? null);
  $b2Out = $isPending ? ($req?->requested_break2_out ?? null) : ($break2->break_out ?? null);

  $note = $req?->note ?? ($attendance->note ?? '');
@endphp

<div class="container">
  <h2 class="page-title">勤怠詳細</h2>

  <div class="card">
    <div class="row">
      <div class="label">名前</div>
      <div class="value">{{ $user->name ?? ($attendance->user->name ?? '') }}</div>
    </div>

    <div class="row">
      <div class="label">日付</div>
      <div class="value">{{ $dateStr }}</div>
    </div>

    <div class="row">
      <div class="label">出勤・退勤</div>
      <div class="value">
        {{ $fmt($clockIn) }}
        <span>〜</span>
        {{ $fmt($clockOut) }}
      </div>
    </div>

    <div class="row">
      <div class="label">休憩</div>
      <div class="value">
        {{ $fmt($b1In) }}
        <span>〜</span>
        {{ $fmt($b1Out) }}
      </div>
    </div>

    <div class="row">
      <div class="label">休憩2</div>
      <div class="value">
        {{ $fmt($b2In) }}
        <span>〜</span>
        {{ $fmt($b2Out) }}
      </div>
    </div>

    <div class="row">
      <div class="label">備考</div>
      <div class="value">{{ $note }}</div>
    </div>
  </div>

  <div class="actions">
    @if(($req?->status ?? '') === 'approved')
      <button type="button" class="btn" disabled>承認済み</button>
    @else
      <form method="POST" action="{{ route('admin.stamp_correction_requests.approve', $req) }}">
        @csrf
        <button type="submit" class="btn">承認</button>
      </form>
    @endif
  </div>
</div>
@endsection
