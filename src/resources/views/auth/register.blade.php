@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
@endsection

@section('content')
<div class="auth">
  <div class="auth__box">
    <h1 class="auth__title">会員登録</h1>

    <form class="form" method="POST" action="{{ route('register.post') }}">
      @csrf

      <div class="form__group">
        <label class="form__label">お名前</label>
        <input class="form__input" type="text" name="name" value="{{ old('name') }}" autocomplete="name">

        <p class="form__error @error('name') is-visible @enderror">
          @error('name'){{ $message }}@enderror
        </p>
      </div>

      <div class="form__group">
        <label class="form__label">メールアドレス</label>
        <input class="form__input" type="email" name="email" value="{{ old('email') }}" autocomplete="email">

        <p class="form__error @error('email') is-visible @enderror">
          @error('email'){{ $message }}@enderror
        </p>
      </div>

      <div class="form__group">
        <label class="form__label">パスワード</label>
        <input class="form__input" type="password" name="password" autocomplete="new-password">

        <p class="form__error @error('password') is-visible @enderror">
          @error('password'){{ $message }}@enderror
        </p>
      </div>

      <div class="form__group">
        <label class="form__label">パスワード確認</label>
        <input class="form__input" type="password" name="password_confirmation" autocomplete="new-password">

        <p class="form__error @error('password_confirmation') is-visible @enderror">
          @error('password_confirmation'){{ $message }}@enderror
        </p>
      </div>

      <button class="btn btn--black btn--wide" type="submit">登録する</button>
    </form>

    <div class="auth__link">
      <a href="{{ route('login') }}">ログインはこちら</a>
    </div>
  </div>
</div>
@endsection
