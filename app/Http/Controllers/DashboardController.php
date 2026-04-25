<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Models\EventRegistration;
use App\Models\UserProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(Request $request): View
    {
        $request->user()->loadMissing(['epiChannel']);

        $activeUserProductsCount = UserProduct::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->count();

        $activeCoursesCount = UserProduct::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->whereHas('product', fn ($q) => $q->where('product_type', ProductType::Course->value))
            ->count();

        $activeEventsCount = EventRegistration::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->count();

        $channel = $request->user()->epiChannel;
        $epiChannelStatus = $channel
            ? match ($channel->status->value) {
                'active' => 'Aktif',
                'prospect' => 'Prospect',
                'qualified' => 'Qualified',
                'suspended' => 'Suspended',
                'inactive' => 'Tidak aktif',
                default => 'Belum aktif',
            }
            : 'Belum aktif';
        $epiChannelDescription = $channel && $channel->isActive()
            ? 'Dashboard penghasilan tersedia'
            : 'Aktivasi melalui OMS/Admin';

        return view('dashboard', [
            'activeUserProductsCount' => $activeUserProductsCount,
            'activeCoursesCount' => $activeCoursesCount,
            'activeEventsCount' => $activeEventsCount,
            'epiChannel' => $channel,
            'epiChannelStatus' => $epiChannelStatus,
            'epiChannelDescription' => $epiChannelDescription,
        ]);
    }
}

