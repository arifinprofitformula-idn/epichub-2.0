<?php

namespace App\Filament\Resources\CourseLessons;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\CourseLessons\Pages\CreateCourseLesson;
use App\Filament\Resources\CourseLessons\Pages\EditCourseLesson;
use App\Filament\Resources\CourseLessons\Pages\ListCourseLessons;
use App\Filament\Resources\CourseLessons\Schemas\CourseLessonForm;
use App\Filament\Resources\CourseLessons\Tables\CourseLessonsTable;
use App\Models\CourseLesson;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CourseLessonResource extends Resource
{
    protected static ?string $model = CourseLesson::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Materi Kelas';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Program;

    public static function form(Schema $schema): Schema
    {
        return CourseLessonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseLessonsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseLessons::route('/'),
            'create' => CreateCourseLesson::route('/create'),
            'edit' => EditCourseLesson::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
