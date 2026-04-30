<?php

use App\Http\Controllers\Api\Oms\EpiChannelCreateAccountController;
use App\Http\Controllers\Oms\OmsEpiChannelController;
use Illuminate\Support\Facades\Route;

Route::get('/oms/epi-channel/create-account', static function () {
    return response()->json([
        'response_code' => (string) config('epichub.oms.response.failed', '99'),
        'message' => 'Gagal',
        'error' => 'Gunakan method POST untuk endpoint ini.',
        'allowed_method' => 'POST',
        'endpoint' => url('/api/oms/epi-channel/create-account'),
    ], 405);
});

Route::middleware(['oms.signature', 'throttle:60,1'])->prefix('oms/epi-channel')->group(function () {
    Route::post('/create-account', EpiChannelCreateAccountController::class)->name('api.oms.epi-channel.create-account');
    Route::post('/change-password', [OmsEpiChannelController::class, 'changePassword'])->name('oms.epi-channel.change-password');
});
