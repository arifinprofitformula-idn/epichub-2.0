<?php

use App\Http\Controllers\Oms\OmsEpiChannelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['oms', 'throttle:60,1'])->prefix('oms/epi-channel')->name('oms.epi-channel.')->group(function () {
    Route::post('/create-account', [OmsEpiChannelController::class, 'createAccount'])->name('create-account');
    Route::post('/change-password', [OmsEpiChannelController::class, 'changePassword'])->name('change-password');
});

