<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    public function list()
    {
        $users = User::query()
            ->orderBy('id')
            ->get(['id', 'name', 'email']);

        return view('admin.staff.list', compact('users'));
    }

    public function attendance(Request $request, User $user)
    {
        abort(404);
    }
}
