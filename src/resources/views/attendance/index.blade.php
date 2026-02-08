@extends('layouts.app')

@section('content')
@php
  $status = $attendance?->status ?? 'off';

  $statusLabel = $status === 'off'
    ? '勤務外'
    : ($status === 'working'
      ? '出勤中'
      : ($status === 'break' ? '休憩中' : '退勤済'));
@endphp

<div class="attendance-page" style="text-align:center; margin-top:40px;">
  <div style="margin-bottom:10px;">
    <span style="padding:4px 12px; background:#ddd; border-radius:999px;">
      {{ $statusLabel }}
    </span>
  </div>

  <h2>{{ \Carbon\Carbon::now()->format('Y年n月j日（D）') }}</h2>

  <div style="font-size:72px; font-weight:bold; margin:20px 0;">
    {{ \Carbon\Carbon::now()->format('H:i') }}
  </div>

  @if (session('status'))
    <p style="margin:0 0 18px;">{{ session('status') }}</p>
  @endif

  @if ($errors->has('clock'))
    <p style="margin:0 0 18px; color:#d93025;">{{ $errors->first('clock') }}</p>
  @endif

  @if ($status === 'off')
    <form method="POST" action="{{ route('attendance.clockIn') }}">
      @csrf
      <button type="submit" class="attendance-btn attendance-btn--black">出勤</button>
    </form>

  @elseif ($status === 'working')
    <div style="display:flex; justify-content:center; gap:26px;">
      <form method="POST" action="{{ route('attendance.clockOut') }}">
        @csrf
        <button type="submit" class="attendance-btn attendance-btn--black">退勤</button>
      </form>

      <form method="POST" action="{{ route('attendance.breakStart') }}">
        @csrf
        <button type="submit" class="attendance-btn attendance-btn--white">休憩入</button>
      </form>
    </div>

  @elseif ($status === 'break')
    <form method="POST" action="{{ route('attendance.breakEnd') }}">
      @csrf
      <button type="submit" class="attendance-btn attendance-btn--white">休憩戻</button>
    </form>

  @else
    @if (!session('status'))
      <p style="margin:0 0 18px;">お疲れ様でした。</p>
    @endif
  @endif
</div>
@endsection
