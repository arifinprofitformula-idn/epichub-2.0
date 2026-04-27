<?php

use App\Enums\CourseStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Enums\UserProductStatus;
use App\Models\Course;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Str;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard katalog hanya menampilkan course yang belum dimiliki user', function () {
    $user = User::factory()->create();
    $ownedProduct = createDashboardCourseProduct('dashboard-owned-course', 'Course Sudah Dimiliki');
    $availableProduct = createDashboardCourseProduct('dashboard-available-course', 'Course Belum Dimiliki');

    UserProduct::query()->create([
        'user_id' => $user->id,
        'product_id' => $ownedProduct->id,
        'status' => UserProductStatus::Active,
        'granted_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(route('checkout.show', $ownedProduct->slug), false)
        ->assertSee('Course Belum Dimiliki')
        ->assertSee(route('checkout.show', $availableProduct->slug), false);
});

function createDashboardCourseProduct(string $slug, string $title): Product
{
    $product = Product::query()->create([
        'title' => $title,
        'slug' => $slug.'-'.Str::lower(Str::random(6)),
        'product_type' => ProductType::Course,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ]);

    Course::query()->create([
        'product_id' => $product->id,
        'title' => $title,
        'slug' => 'course-'.$slug.'-'.Str::lower(Str::random(6)),
        'status' => CourseStatus::Published,
        'published_at' => now(),
    ]);

    return $product;
}
