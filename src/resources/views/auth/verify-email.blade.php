@extends('layouts.app')

@section('content')
<div style="max-width: 800px; margin: 40px auto; background: #fff; padding: 40px; text-align: center; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

    <h2 style="margin-bottom:20px;">メール認証</h2>

    <p style="margin-bottom: 30px;">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    @if (session('status') === 'verification-link-sent')
        <p style="color: green; margin-top: 20px;">
            認証メールを再送しました。受信箱を確認してください。
        </p>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" style="margin-top: 20px;">
        @csrf
        <button type="submit" style="padding:10px 18px; border:none; border-radius:6px; background:#000; color:#fff; cursor:pointer;">
            認証メールを再送する
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" style="margin-top: 20px;">
        @csrf
        <button type="submit" style="background:none;border:none;color:#3490dc;text-decoration:underline;cursor:pointer;">
            ログアウト
        </button>
    </form>
</div>
@endsection
