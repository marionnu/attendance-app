<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'COACHTECH') }}</title>

  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}">
  @stack('styles')
  @yield('css')
</head>

<body>
  <header class="header">
    <div class="header__inner">

      @auth
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="header__logo" aria-label="ログアウト">
            <img src="{{ asset('svg/logo.svg') }}" alt="COACHTECH" class="header__logo-img">
          </button>
        </form>
      @else
        <a class="header__logo" href="{{ route('login') }}" aria-label="COACHTECH">
          <img src="{{ asset('svg/logo.svg') }}" alt="COACHTECH" class="header__logo-img">
        </a>
      @endauth

      @auth
        <nav class="header__nav">
          @if (auth()->user()->is_admin ?? false)
            {{-- 管理者メニュー --}}
            <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
            <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
            <a href="{{ route('admin.stamp_correction_requests.index') }}">申請</a>
          @else
            {{-- スタッフメニュー --}}
            <a href="{{ route('attendance.index') }}">勤怠</a>
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
            <a href="{{ route('stamp_correction_request.list') }}">申請</a>
          @endif

          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="header__logout">ログアウト</button>
          </form>
        </nav>
      @endauth

    </div>
  </header>

  <main class="main">
    @yield('content')
  </main>

  @yield('scripts')
</body>
</html>
