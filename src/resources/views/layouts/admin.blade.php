<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}">
  @yield('css')
  <title>{{ config('app.name', 'COACHTECH') }}</title>
</head>
<body>
<header class="header">
  <div class="header__inner">
    <div class="header__logo">
  <a href="#"
     onclick="event.preventDefault(); document.getElementById('admin-logo-logout-form').submit();">
    <img src="{{ asset('svg/logo.svg') }}" alt="COACHTECH" class="header__logo-img">
  </a>

  <form id="admin-logo-logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
    @csrf
  </form>
</div>

    <nav class="header__nav">
      <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
      <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
      <a href="{{ route('admin.stamp_correction_requests.index', ['tab' => 'pending']) }}">申請一覧</a>
      <form method="POST" action="{{ route('logout') }}" style="display:inline;">
        @csrf
        <button type="submit" class="header__logout">ログアウト</button>
      </form>
    </nav>
  </div>
</header>

<main>
  @yield('content')
</main>
</body>
</html>
