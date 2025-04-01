<?php

use App\Admin\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix(config('admin.route_prefix'))->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
});