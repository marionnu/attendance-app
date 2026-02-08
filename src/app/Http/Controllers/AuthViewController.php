<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AdminLoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthViewController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function showAdminLogin()
    {
        session(['url.intended' => route('admin.attendance.list')]);

        return view('admin.login');
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return back()
                ->withErrors(['email' => 'メールアドレスまたはパスワードが違います'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        if (auth()->user()->is_admin ?? false) {
            $request->session()->forget('url.intended');

            return redirect()->route('admin.attendance.list');
        }

        return redirect()->route('attendance.index');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('attendance.index');
    }

    public function adminLogin(AdminLoginRequest $request)
    {
        $data = $request->validated();

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return back()
                ->withErrors(['email' => 'メールアドレスまたはパスワードが違います'])
                ->onlyInput('email');
        }

        if (!(auth()->user()->is_admin ?? false)) {
            Auth::logout();

            return back()
                ->withErrors(['email' => '管理者アカウントではありません'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.attendance.list'));
    }
}
