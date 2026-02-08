@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
<div class="container">
  <h2 class="page-title">申請一覧</h2>

  <div class="tabs">
    <a class="tab {{ $tab === 'pending' ? 'is-active' : '' }}"
       href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}">承認待ち</a>

    <a class="tab {{ $tab === 'approved' ? 'is-active' : '' }}"
       href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}">承認済み</a>
  </div>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>状態</th>
          <th>名前</th>
          <th>対象日時</th>
          <th>申請理由</th>
          <th>申請日時</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $r)
          <tr>
            <td>{{ $r->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
            <td>{{ $r->user->name ?? auth()->user()->name }}</td>
            <td>{{ \Carbon\Carbon::parse($r->target_date)->format('Y/m/d') }}</td>
            <td>{{ $r->note }}</td>
            <td>{{ $r->created_at->format('Y/m/d') }}</td>
            <td>
              <a class="link" href="{{ route('attendance.detail', ['attendance' => $r->attendance_id, 'request_id' => $r->id]) }}">
                詳細
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="empty">申請がありません</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
