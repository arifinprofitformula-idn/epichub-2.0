<?php

namespace App\Http\Controllers\Catalog;

use App\Enums\LessonProgressStatus;
use App\Enums\ProductType;
use App\Models\CourseLesson;
use App\Models\EventRegistration;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\UserProduct;
use Illuminate\Database\Eloquent\Builder;
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
        $ownedUserProduct = null;
        $eventRegistration = null;
        $progress = null;
        $accessUrl = null;
        $primaryLabel = null;
        $showAppLayout = auth()->check();

        if (auth()->check()) {
            $user = auth()->user();
            $user->loadMissing('epiChannel');
            $viewerChannel = $user->epiChannel;

            $ownedUserProduct = UserProduct::query()
                ->where('user_id', $user->id)
                ->active()
                ->where('product_id', $product->id)
                ->with(['product.course', 'product.event'])
                ->latest('granted_at')
                ->first();

            if ($ownedUserProduct) {
                $typeValue = $product->product_type?->value ?? (string) $product->product_type;

                if ($typeValue === ProductType::Course->value) {
                    $courseId = $ownedUserProduct->product?->course?->id;

                    if ($courseId) {
                        $totalLessons = (int) CourseLesson::query()
                            ->where('course_id', $courseId)
                            ->where('is_active', true)
                            ->where(function (Builder $query): void {
                                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
                            })
                            ->count();

                        $completedLessons = (int) \App\Models\LessonProgress::query()
                            ->where('user_id', $user->id)
                            ->where('course_id', $courseId)
                            ->where('status', LessonProgressStatus::Completed)
                            ->count();

                        $progress = [
                            'total' => $totalLessons,
                            'completed' => $completedLessons,
                            'percent' => $totalLessons === 0 ? 0 : (int) round(($completedLessons / $totalLessons) * 100),
                        ];
                    }
                }

                if ($typeValue === ProductType::Event->value && $product->event) {
                    $eventRegistration = EventRegistration::query()
                        ->where('user_id', $user->id)
                        ->where('event_id', $product->event->id)
                        ->active()
                        ->latest('registered_at')
                        ->first();
                }

                [$accessUrl, $primaryLabel] = match ($typeValue) {
                    ProductType::Course->value => [route('my-courses.show', $ownedUserProduct), 'Masuk Kelas'],
                    ProductType::Event->value => [$eventRegistration ? route('my-events.show', $eventRegistration) : route('my-events.index'), 'Lihat Event'],
                    ProductType::Ebook->value => [route('my-products.show', $ownedUserProduct), 'Baca Ebook'],
                    ProductType::DigitalFile->value => [route('my-products.show', $ownedUserProduct), 'Unduh File'],
                    ProductType::Bundle->value => [route('my-products.show', $ownedUserProduct), 'Akses Bundle'],
                    ProductType::Membership->value => [route('my-products.show', $ownedUserProduct), 'Lihat Akses'],
                    default => [route('my-products.show', $ownedUserProduct), 'Lihat Akses'],
                };
            }
        }

        abort_unless($product->status?->value === 'published', 404);
        abort_unless($product->visibility?->value === 'public', 404);
        abort_if($product->publish_at !== null && $product->publish_at->isFuture(), 404);

        $view = auth()->check()
            ? 'catalog.products.show-app'
            : 'catalog.products.show-public';

        return view($view, [
            'product' => $product,
            'viewerChannel' => $viewerChannel,
            'ownedUserProduct' => $ownedUserProduct,
            'eventRegistration' => $eventRegistration,
            'progress' => $progress,
            'accessUrl' => $accessUrl,
            'primaryLabel' => $primaryLabel,
        ]);
    }
}
