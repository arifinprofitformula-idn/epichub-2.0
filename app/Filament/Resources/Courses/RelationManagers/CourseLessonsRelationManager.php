<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Enums\CourseLessonType;
use App\Filament\Resources\CourseLessons\Schemas\CourseLessonForm;
use App\Models\CourseLesson;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseLessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'Materi / Lesson';

    public function form(Schema $schema): Schema
    {
        return $schema->components(
            CourseLessonForm::getComponents([
                'course_selectable' => false,
                'course_id' => $this->getOwnerRecord()->getKey(),
            ]),
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([SoftDeletingScope::class])
                ->with('section')
                ->orderBy('course_section_id')
                ->orderBy('sort_order'))
            ->defaultSort('sort_order')
            ->defaultGroup(
                Group::make('section.title')
                    ->label('Modul')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (CourseLesson $record): string => $record->section?->title ?? 'Tanpa Modul'),
            )
            ->groups([
                Group::make('section.title')
                    ->label('Modul')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (CourseLesson $record): string => $record->section?->title ?? 'Tanpa Modul'),
            ])
            ->reorderable('sort_order')
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('section.title')
                    ->label('Modul')
                    ->placeholder('Tanpa modul')
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('Materi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lesson_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (CourseLessonType|string|null $state): string => $state instanceof CourseLessonType ? $state->label() : (string) $state)
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                IconColumn::make('is_preview')
                    ->label('Preview')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('course_section_id')
                    ->label('Modul')
                    ->options(fn () => $this->getOwnerRecord()->sections()->orderBy('sort_order')->pluck('title', 'id')),
                SelectFilter::make('lesson_type')
                    ->label('Tipe')
                    ->options(collect(CourseLessonType::cases())->mapWithKeys(fn (CourseLessonType $type) => [$type->value => $type->label()])->all()),
                TernaryFilter::make('is_active')
                    ->label('Aktif'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Materi')
                    ->slideOver()
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'course_id' => $this->getOwnerRecord()->getKey(),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->slideOver()
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'course_id' => $this->getOwnerRecord()->getKey(),
                    ]),
                DeleteAction::make()
                    ->label('Hapus'),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->lessons()->count();
    }
}
