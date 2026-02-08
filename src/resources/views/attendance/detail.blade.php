@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
@php
  $breaks = $attendance->breaks ?? collect();
  $b1 = $breaks->get(0);
  $b2 = $breaks->get(1);

  $fmt = fn($dt) => $dt ? \Carbon\Carbon::parse($dt)->format('H:i') : '';

  $isPending = !empty($pendingRequest);

  $clockIn  = $isPending ? $pendingRequest->requested_clock_in  : $fmt($attendance->clock_in);
  $clockOut = $isPending ? $pendingRequest->requested_clock_out : $fmt($attendance->clock_out);

  $break1In  = $isPending ? ($pendingRequest->requested_break1_in  ?? '') : $fmt(optional($b1)->break_in);
  $break1Out = $isPending ? ($pendingRequest->requested_break1_out ?? '') : $fmt(optional($b1)->break_out);

  $break2In  = $isPending ? ($pendingRequest->requested_break2_in  ?? '') : $fmt(optional($b2)->break_in);
  $break2Out = $isPending ? ($pendingRequest->requested_break2_out ?? '') : $fmt(optional($b2)->break_out);

  $noteValue = $isPending
    ? ($pendingRequest->note ?? $pendingRequest->reason ?? '')
    : '';
@endphp

<div class="container">
  <h2 class="page-title">勤怠詳細</h2>

  @if ($errors->any())
    <div class="form__error-summary">
      @foreach ($errors->all() as $msg)
        <p>{{ $msg }}</p>
      @endforeach
    </div>
  @endif

  @if (session('status'))
    <p>{{ session('status') }}</p>
  @endif

  <form method="POST" action="{{ route('stamp_correction_request.store', $attendance->id) }}">
    @csrf

    <div class="card">
      <div class="row">
        <div class="label">名前</div>
        <div class="value">{{ auth()->user()->name }}</div>
      </div>

      <div class="row">
        <div class="label">日付</div>
        <div class="value">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</div>
      </div>

      <div class="row">
        <div class="label">出勤・退勤</div>
        <div class="value inputs">
          <input type="time" name="clock_in" value="{{ old('clock_in', $clockIn) }}" {{ $isPending ? 'disabled' : '' }}>
          <span>〜</span>
          <input type="time" name="clock_out" value="{{ old('clock_out', $clockOut) }}" {{ $isPending ? 'disabled' : '' }}>
        </div>
      </div>

      <div class="row">
        <div class="label">休憩</div>
        <div class="value inputs">
          <input type="time" name="break1_in" value="{{ old('break1_in', $break1In) }}" {{ $isPending ? 'disabled' : '' }}>
          <span>〜</span>
          <input type="time" name="break1_out" value="{{ old('break1_out', $break1Out) }}" {{ $isPending ? 'disabled' : '' }}>
        </div>
      </div>

      <div class="row">
        <div class="label">休憩2</div>
        <div class="value inputs">
          <input type="time" name="break2_in" value="{{ old('break2_in', $break2In) }}" {{ $isPending ? 'disabled' : '' }}>
          <span>〜</span>
          <input type="time" name="break2_out" value="{{ old('break2_out', $break2Out) }}" {{ $isPending ? 'disabled' : '' }}>
        </div>
      </div>

      <div class="row">
        <div class="label">備考</div>
        <div class="value">
          <input type="text" name="note" value="{{ old('note', $noteValue) }}" {{ $isPending ? 'disabled' : '' }}>
        </div>
      </div>
    </div>

    @unless($isPending)
      <div class="actions">
        <button type="submit" class="btn">修正</button>
      </div>
    @endunless
  </form>

  @if ($isPending)
    <p class="notice-bottom">※承認待ちのため修正はできません。</p>
  @endif

</div>
@endsection
