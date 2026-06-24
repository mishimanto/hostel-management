<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\LeaveRequestController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\RoomChangeController;
use App\Models\Branch;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    $branches = Schema::hasTable('branches')
        ? Branch::with(['rooms' => fn ($query) => $query->where('status', 'available')->orderBy('monthly_rent')])
            ->orderBy('name')
            ->get()
        : collect();

    return view('welcome', compact('branches'));
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('customer')->name('customer.')->group(function (): void {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('room-change/calculate', [RoomChangeController::class, 'calculate'])->name('room-change.calculate');
        Route::post('room-change', [RoomChangeController::class, 'store'])->name('room-change.store');
        Route::post('leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
        Route::get('notifications/live', [NotificationController::class, 'index'])->name('notifications.live');
        Route::post('notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    });

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function (): void {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::post('rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::patch('bookings/{roomBooking}', [AdminDashboardController::class, 'reviewBooking'])->name('bookings.review');
        Route::patch('room-change/{roomChangeRequest}', [AdminDashboardController::class, 'reviewRoomChange'])->name('room-change.review');
        Route::patch('leave-requests/{leaveRequest}', [AdminDashboardController::class, 'reviewLeave'])->name('leave-requests.review');
    });
});
