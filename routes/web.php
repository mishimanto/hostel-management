<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\ExitRequestController;
use App\Http\Controllers\Customer\LeaveApplicationController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\SeatChangeRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function (): void {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('seat-change/calculate', [SeatChangeRequestController::class, 'calculate'])->name('seat-change.calculate');
    Route::post('seat-change', [SeatChangeRequestController::class, 'store'])->name('seat-change.store');

    Route::post('leave-applications', [LeaveApplicationController::class, 'store'])->name('leave.store');

    Route::get('exit-requests/calculate', [ExitRequestController::class, 'calculate'])->name('exit.calculate');
    Route::post('exit-requests', [ExitRequestController::class, 'store'])->name('exit.store');

    Route::get('notifications/live', [NotificationController::class, 'index'])->name('notifications.live');
    Route::post('notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::patch('admin/seat-change/{seatChangeRequest}', [AdminDashboardController::class, 'reviewSeatChange'])->name('admin.seat-change.review');
    Route::patch('admin/leave/{leaveApplication}', [AdminDashboardController::class, 'reviewLeave'])->name('admin.leave.review');
    Route::patch('admin/exit/{exitRequest}', [AdminDashboardController::class, 'reviewExit'])->name('admin.exit.review');
});
