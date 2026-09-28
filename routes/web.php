<?php
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function(){
    Route::get('/login',[AuthController::class,'showLogin'])->name('login');
    Route::post('/login',[AuthController::class,'login'])->name('login.store');
    Route::get('/register',[AuthController::class,'showRegister'])->name('register');
    Route::post('/register',[AuthController::class,'register'])->name('register.store');
    Route::get('/forgot-password',[AuthController::class,'showForgot'])->name('password.request');
    Route::post('/forgot-password',[AuthController::class,'sendReset'])->name('password.email');
    Route::get('/reset-password/{token}',[AuthController::class,'showReset'])->name('password.reset');
    Route::post('/reset-password',[AuthController::class,'reset'])->name('password.update');
});
Route::get('/calendar/feed/{token}',[CalendarController::class,'feed'])->where('token','[A-Za-z0-9]+')->name('calendar.feed');

Route::middleware('auth')->group(function(){
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
    Route::get('/',[DashboardController::class,'index'])->name('dashboard');
    Route::get('/leave-requests',[LeaveRequestController::class,'index'])->name('leave-requests.index');
    Route::get('/leave-requests/create',[LeaveRequestController::class,'create'])->name('leave-requests.create');
    Route::post('/leave-requests',[LeaveRequestController::class,'store'])->name('leave-requests.store');
    Route::get('/leave-requests/{leaveRequest}',[LeaveRequestController::class,'show'])->name('leave-requests.show');
    Route::post('/leave-requests/{leaveRequest}/cancel',[LeaveRequestController::class,'cancel'])->name('leave-requests.cancel');
    Route::get('/calendar',[CalendarController::class,'index'])->name('calendar.index');
    Route::get('/notifications',[NotificationController::class,'index'])->name('notifications.index');
    Route::post('/notifications/read-all',[NotificationController::class,'readAll'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read',[NotificationController::class,'read'])->name('notifications.read');

    Route::middleware('role:Manager')->group(function(){
        Route::get('/approvals',[ApprovalController::class,'index'])->name('approvals.index');
        Route::post('/approvals/{leaveRequest}/approve',[ApprovalController::class,'approve'])->name('approvals.approve');
        Route::post('/approvals/{leaveRequest}/reject',[ApprovalController::class,'reject'])->name('approvals.reject');
    });
    Route::middleware('role:Administrator')->group(function(){
        Route::get('/users',[UserController::class,'index'])->name('users.index');
        Route::put('/users/{user}',[UserController::class,'update'])->name('users.update');
        Route::resource('/leave-types',LeaveTypeController::class)->except(['show'])->parameters(['leave-types'=>'leaveType']);
    });
});
