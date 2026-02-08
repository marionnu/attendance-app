@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
@endsection

@section('content')
<div class="auth">
  <div class="auth__box">
    <h1 class="auth__title">ログイン</h1>

    <form class="form" method="POST" action="{{ route('login.post') }}">
      @csrf

      <div class="form__group">
        <label class="form__label">メールアドレス</label>
        <input class="form__input" type="email" name="email" value="{{ old('email') }}" autocomplete="email">

        <p class="form__error @error('email') is-visible @enderror">
          @error('email'){{ $message }}@enderror
        </p>
      </div>

      <div class="form__group">
        <label class="form__label">パスワード</label>
        <input class="form__input" type="password" name="password" autocomplete="current-password">

        <p class="form__error @error('password') is-visible @enderror">
          @error('password'){{ $message }}@enderror
        </p>
      </div>

      <button class="btn btn--black btn--wide" type="submit">ログインする</button>
    </form>

    <div class="auth__link">
      <a href="{{ route('register') }}">会員登録はこちら</a>
    </div>
  </div>
</div>
@endsection
