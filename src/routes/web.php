<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthViewController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStampCorrectionRequestController;
use App\Http\Controllers\AdminStaffController;

Route::get('/', function () {
    $user = auth()->user();

    if ($user && ($user->is_admin ?? false)) {
        return redirect()->route('admin.attendance.list');
    }

    return redirect()->route('attendance.index');
})->middleware(['auth', 'verified'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthViewController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthViewController::class, 'login'])->name('login.post');

    Route::get('/register', [AuthViewController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthViewController::class, 'register'])->name('register.post');

    Route::get('/admin/login', [AuthViewController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthViewController::class, 'adminLogin'])->name('admin.login.post');
});

Route::post('/logout', function (Request $request) {
    $isAdmin = (bool) (auth()->user()->is_admin ?? false);

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return $isAdmin
        ? redirect()->route('admin.login')
        : redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/list', [AttendanceController::class, 'list'])->name('list');
        Route::get('/detail/{attendance}', [AttendanceController::class, 'detail'])->name('detail');
        Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clockIn');
        Route::post('/break-in', [AttendanceController::class, 'breakStart'])->name('breakStart');
        Route::post('/break-out', [AttendanceController::class, 'breakEnd'])->name('breakEnd');
        Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('clockOut');
    });

    Route::prefix('stamp_correction_request')->name('stamp_correction_request.')->group(function () {
        Route::get('/list', [StampCorrectionRequestController::class, 'index'])->name('list');
        Route::post('/{attendance}/store', [StampCorrectionRequestController::class, 'store'])->name('store');
    });
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])
            ->name('attendance.list');
        Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])
            ->name('attendance.show');
        Route::post('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])
            ->name('attendance.update');
        Route::get('/staff/list', [AdminStaffController::class, 'list'])
            ->name('staff.list');
        Route::get('/attendance/staff/{user}', [\App\Http\Controllers\AdminStaffAttendanceController::class, 'index'])
            ->name('attendance.staff.index');
        Route::get('/attendance/staff/{user}/export', [\App\Http\Controllers\AdminStaffAttendanceController::class, 'export'])
            ->name('attendance.staff.export');
        Route::get('/stamp_correction_requests', [AdminStampCorrectionRequestController::class, 'index'])
            ->name('stamp_correction_requests.index');
        Route::get('/stamp_correction_requests/{stampCorrectionRequest}', [AdminStampCorrectionRequestController::class, 'show'])
            ->name('stamp_correction_requests.show');
        Route::post('/stamp_correction_requests/{stampCorrectionRequest}/approve', [AdminStampCorrectionRequestController::class, 'approve'])
            ->name('stamp_correction_requests.approve');

    });
});

Route::middleware('auth')->group(function () {

    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('home');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});
