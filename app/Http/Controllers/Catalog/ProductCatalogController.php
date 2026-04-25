<?php

namespace App\Http\Controllers\Catalog;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductCatalogController
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->published()
            ->visiblePublic()
            ->with(['category'])
            ->orderByDesc('is_featured')
            ->orderByDesc('publish_at')
            ->orderBy('sort_order');

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($query) use ($q): void {
                $query
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%");
            });
        }

        $category = trim((string) $request->query('category', ''));
        if ($category !== '') {
            $query->whereHas('category', function ($q) use ($category): void {
                $q->where('slug', $category);
            });
        }

        $type = trim((string) $request->query('type', ''));
        if ($type !== '') {
            $query->where('product_type', $type);
        }

        $products = $query->paginate(12)->withQueryString();

        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return view('catalog.products.index', [
            'products' => $products,
            'categories' => $categories,
            'q' => $q,
            'activeCategory' => $category,
            'activeType' => $type,
        ]);
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'files', 'bundledProducts']);
        $viewerChannel = null;

        if (auth()->check()) {
            auth()->user()->loadMissing('epiChannel');
            $viewerChannel = auth()->user()->epiChannel;
        }

        abort_unless($product->status?->value === 'published', 404);
        abort_unless($product->visibility?->value === 'public', 404);
        abort_if($product->publish_at !== null && $product->publish_at->isFuture(), 404);

        return view('catalog.products.show', [
            'product' => $product,
            'viewerChannel' => $viewerChannel,
        ]);
    }
}
