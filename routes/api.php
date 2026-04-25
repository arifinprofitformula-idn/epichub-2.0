<?php

use App\Http\Controllers\Oms\OmsAccountController;
use App\Http\Controllers\Oms\OmsPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware(['oms'])->prefix('oms')->name('oms.')->group(function () {
    Route::post('/accounts', [OmsAccountController::class, 'store'])->name('accounts.store');
    Route::post('/passwords/change', [OmsPasswordController::class, 'change'])->name('passwords.change');
});

