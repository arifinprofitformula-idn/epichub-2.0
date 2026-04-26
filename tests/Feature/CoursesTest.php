<?php

use App\Enums\AccessLogAction;
use App\Enums\CourseLessonType;
use App\Enums\CourseStatus;
use App\Enums\LessonProgressStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Enums\UserProductStatus;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\LessonProgress;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Facades\Storage;

function makeCourseProduct(array $overrides = []): Product
{
    return Product::query()->create(array_merge([
        'title' => 'Product Course',
        'slug' => 'product-course-'.uniqid(),
        'product_type' => ProductType::Course,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ], $overrides));
}

function makePublishedCourse(Product $product, array $overrides = []): Course
{
    return Course::query()->create(array_merge([
        'product_id' => $product->id,
        'title' => 'Kelas A',
        'slug' => 'kelas-a-'.uniqid(),
        'status' => CourseStatus::Published,
        'published_at' => now(),
    ], $overrides));
}

function makeActiveCourseEntitlement(User $user, Product $product, array $overrides = []): UserProduct
{
    return UserProduct::query()->create(array_merge([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => UserProductStatus::Active,
        'granted_at' => now(),
    ], $overrides));
}

test('user with course entitlement can open /kelas-saya', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    makePublishedCourse($product);
    makeActiveCourseEntitlement($user, $product);

    $this->actingAs($user);
    $this->get(route('my-courses.index'))->assertOk();
});

test('user with entitlement can open course overview and course_accessed is logged', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    CourseLesson::query()->create([
        'course_id' => $course->id,
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
        'lesson_type' => CourseLessonType::Article,
        'content' => '<p>Halo</p>',
        'sort_order' => 0,
        'is_active' => true,
        'published_at' => now(),
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.show', $userProduct))
        ->assertOk()
        ->assertSee('Lesson 1');

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::CourseAccessed->value,
    ]);
});

test('user without entitlement cannot open another user course overview', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $product = makeCourseProduct();
    makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($owner, $product);

    $this->actingAs($other);
    $this->get(route('my-courses.show', $userProduct))->assertNotFound();

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $other->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::AccessDenied->value,
    ]);
});

test('user cannot open lesson from another course', function () {
    $user = User::factory()->create();

    $productA = makeCourseProduct(['slug' => 'course-a-'.uniqid(), 'title' => 'Course A Product']);
    $courseA = makePublishedCourse($productA, ['slug' => 'course-a-'.uniqid(), 'title' => 'Course A']);
    $userProductA = makeActiveCourseEntitlement($user, $productA);

    $productB = makeCourseProduct(['slug' => 'course-b-'.uniqid(), 'title' => 'Course B Product']);
    $courseB = makePublishedCourse($productB, ['slug' => 'course-b-'.uniqid(), 'title' => 'Course B']);

    $lessonB = CourseLesson::query()->create([
        'course_id' => $courseB->id,
        'title' => 'Lesson B1',
        'slug' => 'lesson-b1',
        'lesson_type' => CourseLessonType::Article,
        'content' => '<p>B</p>',
        'sort_order' => 0,
        'is_active' => true,
        'published_at' => now(),
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.lessons.show', [$userProductA, $lessonB]))->assertNotFound();
});

test('user can mark lesson complete and progress does not duplicate', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $lesson = CourseLesson::query()->create([
        'course_id' => $course->id,
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
        'lesson_type' => CourseLessonType::Article,
        'content' => '<p>Halo</p>',
        'sort_order' => 0,
        'is_active' => true,
        'published_at' => now(),
    ]);

    $this->actingAs($user);
    $this->post(route('my-courses.lessons.complete', [$userProduct, $lesson]))->assertRedirect();
    $this->post(route('my-courses.lessons.complete', [$userProduct, $lesson]))->assertRedirect();

    $this->assertDatabaseCount('lesson_progress', 1);

    $progress = LessonProgress::query()->firstOrFail();
    expect($progress->status)->toBe(LessonProgressStatus::Completed);
    expect($progress->completed_at)->not->toBeNull();

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::LessonCompleted->value,
    ]);
});

test('progress percent changes after lesson completed', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $lesson = CourseLesson::query()->create([
        'course_id' => $course->id,
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
        'lesson_type' => CourseLessonType::Article,
        'content' => '<p>Halo</p>',
        'sort_order' => 0,
        'is_active' => true,
        'published_at' => now(),
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.show', $userProduct))->assertOk()->assertSee('0%');
    $this->post(route('my-courses.lessons.complete', [$userProduct, $lesson]))->assertRedirect();
    $this->get(route('my-courses.show', $userProduct))->assertOk()->assertSee('100%');
});

test('future published_at lesson tetap tampil untuk user yang punya entitlement aktif', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    CourseLesson::query()->create([
        'course_id' => $course->id,
        'title' => 'Lesson Future Timestamp',
        'slug' => 'lesson-future-timestamp',
        'lesson_type' => CourseLessonType::Article,
        'content' => '<p>Halo</p>',
        'sort_order' => 0,
        'is_active' => true,
        'published_at' => now()->addDay(),
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.show', $userProduct))
        ->assertOk()
        ->assertSee('Lesson Future Timestamp');
});

test('revoked entitlement cannot open course', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product, [
        'status' => UserProductStatus::Revoked,
        'revoked_at' => now(),
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.show', $userProduct))->assertNotFound();
});

test('user with active entitlement can open draft course overview', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    CourseLesson::query()->create([
        'course_id' => Course::query()->create([
            'product_id' => $product->id,
            'title' => 'Kelas Draft',
            'slug' => 'kelas-draft-'.uniqid(),
            'status' => CourseStatus::Draft,
            'published_at' => null,
        ])->id,
        'title' => 'Lesson Draft',
        'slug' => 'lesson-draft-'.uniqid(),
        'lesson_type' => CourseLessonType::Article,
        'content' => '<p>Halo</p>',
        'sort_order' => 0,
        'is_active' => true,
        'published_at' => now(),
    ]);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $this->actingAs($user);
    $this->get(route('my-courses.show', $userProduct))
        ->assertOk()
        ->assertSee('Lesson Draft');
});

test('course overview stays unavailable when no course record exists yet', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $this->actingAs($user);
    $this->get(route('my-courses.show', $userProduct))->assertNotFound();
});

test('lesson attachment download is protected and logged', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $lesson = CourseLesson::query()->create([
        'course_id' => $course->id,
        'title' => 'Attachment Lesson',
        'slug' => 'attachment-1',
        'lesson_type' => CourseLessonType::FileAttachment,
        'attachment_path' => 'courses/attachments/a.pdf',
        'sort_order' => 0,
        'is_active' => true,
        'published_at' => now(),
    ]);

    Storage::disk('local')->put('courses/attachments/a.pdf', 'pdf');

    $this->actingAs($user);
    $this->get(route('my-courses.lessons.download', [$userProduct, $lesson]))->assertOk();

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::LessonAttachmentDownloaded->value,
    ]);
});

