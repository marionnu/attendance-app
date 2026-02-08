@extends('layouts.admin')

@section('content')
<div class="admin-page">
  <div class="admin-page__inner">
    <div class="admin-card">

      <h1 class="admin-title">スタッフ一覧</h1>

      <table class="admin-table">
        <thead>
          <tr>
            <th>名前</th>
            <th>メールアドレス</th>
            <th class="is-center">月次勤怠</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($users as $user)
            <tr>
              <td>{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td class="is-center">
                <a class="admin-link" href="{{ route('admin.attendance.staff.index', $user) }}">詳細</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

    </div>
  </div>
</div>
@endsection
