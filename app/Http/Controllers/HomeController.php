<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController
{
    public function __invoke(): View
    {
        $featuredProducts = Product::query()
            ->published()
            ->visiblePublic()
            ->featured()
            ->latest('publish_at')
            ->limit(3)
            ->get();

        return view('welcome', [
            'featuredProducts' => $featuredProducts,
        ]);
    }
}
