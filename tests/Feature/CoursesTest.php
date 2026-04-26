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
        'lesson_access_mode' => 'free',
        'show_locked_lessons' => true,
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

function makeCourseSectionRecord(Course $course, array $overrides = []): CourseSection
{
    return CourseSection::query()->create(array_merge([
        'course_id' => $course->id,
        'title' => 'Section '.uniqid(),
        'sort_order' => 0,
        'is_active' => true,
    ], $overrides));
}

function makeCourseLessonRecord(Course $course, array $overrides = []): CourseLesson
{
    return CourseLesson::query()->create(array_merge([
        'course_id' => $course->id,
        'course_section_id' => null,
        'title' => 'Lesson '.uniqid(),
        'slug' => 'lesson-'.uniqid(),
        'lesson_type' => CourseLessonType::Article,
        'content' => '<p>Halo</p>',
        'sort_order' => 0,
        'status' => 'published',
        'is_required' => true,
        'is_active' => true,
        'published_at' => now(),
        'available_from' => null,
    ], $overrides));
}

function completeCourseLessonForLearner(User $user, UserProduct $userProduct, Course $course, CourseLesson $lesson): LessonProgress
{
    return LessonProgress::query()->updateOrCreate(
        [
            'user_id' => $user->id,
            'course_lesson_id' => $lesson->id,
        ],
        [
            'course_id' => $course->id,
            'user_product_id' => $userProduct->id,
            'status' => LessonProgressStatus::Completed,
            'completed_at' => now(),
            'last_viewed_at' => now(),
        ],
    );
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

    makeCourseLessonRecord($course, [
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
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

test('course mode free allows user to open second lesson without completing first lesson', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product, ['lesson_access_mode' => 'free']);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    makeCourseLessonRecord($course, [
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
        'sort_order' => 1,
    ]);
    $lessonTwo = makeCourseLessonRecord($course, [
        'title' => 'Lesson 2',
        'slug' => 'lesson-2',
        'sort_order' => 2,
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.lessons.show', [$userProduct, $lessonTwo]))
        ->assertOk()
        ->assertSee('Lesson 2');
});

test('course mode sequential blocks second lesson before first lesson is completed', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product, ['lesson_access_mode' => 'sequential']);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    makeCourseLessonRecord($course, [
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
        'sort_order' => 1,
    ]);
    $lessonTwo = makeCourseLessonRecord($course, [
        'title' => 'Lesson 2',
        'slug' => 'lesson-2',
        'sort_order' => 2,
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.lessons.show', [$userProduct, $lessonTwo]))
        ->assertRedirect(route('my-courses.show', $userProduct));

    $this->followingRedirects()
        ->get(route('my-courses.lessons.show', [$userProduct, $lessonTwo]))
        ->assertSee('Terkunci')
        ->assertSee('Selesaikan materi sebelumnya terlebih dahulu.');
});

test('course mode sequential allows second lesson after first lesson completed', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product, ['lesson_access_mode' => 'sequential']);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $lessonOne = makeCourseLessonRecord($course, [
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
        'sort_order' => 1,
    ]);
    $lessonTwo = makeCourseLessonRecord($course, [
        'title' => 'Lesson 2',
        'slug' => 'lesson-2',
        'sort_order' => 2,
    ]);

    completeCourseLessonForLearner($user, $userProduct, $course, $lessonOne);

    $this->actingAs($user);
    $this->get(route('my-courses.lessons.show', [$userProduct, $lessonTwo]))
        ->assertOk()
        ->assertSee('Lesson 2');
});

test('optional lesson does not block next lesson in sequential mode', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product, ['lesson_access_mode' => 'sequential']);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $lessonOne = makeCourseLessonRecord($course, [
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
        'sort_order' => 1,
        'is_required' => true,
    ]);
    makeCourseLessonRecord($course, [
        'title' => 'Lesson 2 Optional',
        'slug' => 'lesson-2-optional',
        'sort_order' => 2,
        'is_required' => false,
    ]);
    $lessonThree = makeCourseLessonRecord($course, [
        'title' => 'Lesson 3',
        'slug' => 'lesson-3',
        'sort_order' => 3,
        'is_required' => true,
    ]);

    completeCourseLessonForLearner($user, $userProduct, $course, $lessonOne);

    $this->actingAs($user);
    $this->get(route('my-courses.lessons.show', [$userProduct, $lessonThree]))
        ->assertOk()
        ->assertSee('Lesson 3');
});

test('lesson with future available_from cannot be opened', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $lesson = makeCourseLessonRecord($course, [
        'title' => 'Lesson Scheduled',
        'slug' => 'lesson-scheduled',
        'available_from' => now()->addDay(),
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.lessons.show', [$userProduct, $lesson]))
        ->assertRedirect(route('my-courses.show', $userProduct));

    $this->followingRedirects()
        ->get(route('my-courses.lessons.show', [$userProduct, $lesson]))
        ->assertSee('Dijadwalkan')
        ->assertSee('Materi ini dibuka pada');
});

test('lesson with past available_from can be opened', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $lesson = makeCourseLessonRecord($course, [
        'title' => 'Lesson Ready',
        'slug' => 'lesson-ready',
        'available_from' => now()->subHour(),
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.lessons.show', [$userProduct, $lesson]))
        ->assertOk()
        ->assertSee('Lesson Ready');
});

test('lesson draft cannot be opened by learner', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $lesson = makeCourseLessonRecord($course, [
        'title' => 'Lesson Draft',
        'slug' => 'lesson-draft',
        'status' => 'draft',
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.lessons.show', [$userProduct, $lesson]))->assertNotFound();
});

test('user without entitlement cannot open lesson even if they know the URL', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($owner, $product);
    $lesson = makeCourseLessonRecord($course, [
        'title' => 'Private Lesson',
        'slug' => 'private-lesson',
    ]);

    $this->actingAs($intruder);
    $this->get(route('my-courses.lessons.show', [$userProduct, $lesson]))->assertNotFound();
});

test('user cannot open lesson from another course', function () {
    $user = User::factory()->create();

    $productA = makeCourseProduct(['slug' => 'course-a-'.uniqid(), 'title' => 'Course A Product']);
    $courseA = makePublishedCourse($productA, ['slug' => 'course-a-'.uniqid(), 'title' => 'Course A']);
    $userProductA = makeActiveCourseEntitlement($user, $productA);

    $productB = makeCourseProduct(['slug' => 'course-b-'.uniqid(), 'title' => 'Course B Product']);
    $courseB = makePublishedCourse($productB, ['slug' => 'course-b-'.uniqid(), 'title' => 'Course B']);

    $lessonB = makeCourseLessonRecord($courseB, [
        'title' => 'Lesson B1',
        'slug' => 'lesson-b1',
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.lessons.show', [$userProductA, $lessonB]))->assertNotFound();
});

test('user can mark lesson complete idempotently without duplicate progress or duplicate completion logs', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $lesson = makeCourseLessonRecord($course, [
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
    ]);

    $this->actingAs($user);
    $this->post(route('my-courses.lessons.complete', [$userProduct, $lesson]))->assertRedirect();
    $this->post(route('my-courses.lessons.complete', [$userProduct, $lesson]))->assertRedirect();

    $this->assertDatabaseCount('lesson_progress', 1);

    $progress = LessonProgress::query()->firstOrFail();
    expect($progress->status)->toBe(LessonProgressStatus::Completed);
    expect($progress->completed_at)->not->toBeNull();
    expect(
        \App\Models\AccessLog::query()->where('action', AccessLogAction::LessonCompleted->value)->count()
    )->toBe(1);
});

test('progress percent changes after lesson completed', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $lesson = makeCourseLessonRecord($course, [
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.show', $userProduct))->assertOk()->assertSee('0%');
    $this->post(route('my-courses.lessons.complete', [$userProduct, $lesson]))->assertRedirect();
    $this->get(route('my-courses.show', $userProduct))->assertOk()->assertSee('100%');
});

test('show locked lessons false hides inaccessible lessons from overview', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product, [
        'lesson_access_mode' => 'sequential',
        'show_locked_lessons' => false,
    ]);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    makeCourseLessonRecord($course, [
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
        'sort_order' => 1,
    ]);
    makeCourseLessonRecord($course, [
        'title' => 'Lesson 2 Hidden',
        'slug' => 'lesson-2-hidden',
        'sort_order' => 2,
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.show', $userProduct))
        ->assertOk()
        ->assertSee('Lesson 1')
        ->assertDontSee('Lesson 2 Hidden');
});

test('draft course overview is not available to learner', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    Course::query()->create([
        'product_id' => $product->id,
        'title' => 'Kelas Draft',
        'slug' => 'kelas-draft-'.uniqid(),
        'status' => CourseStatus::Draft,
        'published_at' => null,
        'lesson_access_mode' => 'free',
        'show_locked_lessons' => true,
    ]);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $this->actingAs($user);
    $this->get(route('my-courses.show', $userProduct))->assertNotFound();
});

test('future published_at lesson is hidden from learner overview and direct URL', function () {
    $user = User::factory()->create();
    $product = makeCourseProduct();
    $course = makePublishedCourse($product);
    $userProduct = makeActiveCourseEntitlement($user, $product);

    $lesson = makeCourseLessonRecord($course, [
        'title' => 'Lesson Future Publish',
        'slug' => 'lesson-future-publish',
        'published_at' => now()->addDay(),
    ]);

    $this->actingAs($user);
    $this->get(route('my-courses.show', $userProduct))
        ->assertOk()
        ->assertDontSee('Lesson Future Publish');

    $this->get(route('my-courses.lessons.show', [$userProduct, $lesson]))->assertNotFound();
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

    $lesson = makeCourseLessonRecord($course, [
        'title' => 'Attachment Lesson',
        'slug' => 'attachment-1',
        'lesson_type' => CourseLessonType::FileAttachment,
        'attachment_path' => 'courses/attachments/a.pdf',
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
