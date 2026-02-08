@extends('layouts.admin')

@section('content')
@php
  use Carbon\Carbon;

  $currentStatus = request()->query('status', 'pending');

  $statusLabel = function ($status) {
    return match ($status) {
      'pending'  => '承認待ち',
      'approved' => '承認済み',
      default    => $status,
    };
  };

  $fmtDate = fn($d) => $d ? Carbon::parse($d)->format('Y/m/d') : '';
  $fmtDateTime = fn($dt) => $dt ? Carbon::parse($dt)->format('Y/m/d') : '';
@endphp

<div class="admin-page admin-requests">
  <div class="admin-page__inner">
    <div class="admin-card">

      <h1 class="admin-title">申請一覧</h1>

      <div class="admin-tabs">
        <a
          class="admin-tab {{ $currentStatus === 'pending' ? 'is-active' : '' }}"
          href="{{ route('admin.stamp_correction_requests.index', ['status' => 'pending']) }}"
        >
          承認待ち
        </a>

        <a
          class="admin-tab {{ $currentStatus === 'approved' ? 'is-active' : '' }}"
          href="{{ route('admin.stamp_correction_requests.index', ['status' => 'approved']) }}"
        >
          承認済み
        </a>
      </div>

      <table class="admin-table">
        <thead>
          <tr>
            <th class="is-center">状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th class="is-center">詳細</th>
          </tr>
        </thead>

        <tbody>
          @foreach ($requests as $req)
            <tr>
              <td class="is-center">
                <span class="admin-status">
                  {{ $statusLabel($req->status) }}
                </span>
              </td>

              <td>{{ $req->user->name ?? '' }}</td>
              <td>{{ $fmtDate($req->target_date) }}</td>
              <td>{{ $req->note }}</td>
              <td>{{ $fmtDateTime($req->created_at) }}</td>

              <td class="is-center">
                <a class="admin-detail" href="{{ route('admin.stamp_correction_requests.show', $req) }}">
                  詳細
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

    </div>
  </div>
</div>
@endsection
