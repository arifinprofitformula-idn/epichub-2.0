<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Filament\Resources\CourseLessons\Schemas\CourseLessonForm;
use App\Filament\Resources\CourseSections\Schemas\CourseSectionForm;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CourseSectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Bagian / Modul Kelas';

    public function form(Schema $schema): Schema
    {
        return $schema->components(
            CourseSectionForm::getComponents([
                'course_selectable' => false,
                'course_id' => $this->getOwnerRecord()->getKey(),
            ]),
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('lessons')->orderBy('sort_order'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Modul')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lessons_count')
                    ->label('Materi')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Modul')
                    ->slideOver()
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'course_id' => $this->getOwnerRecord()->getKey(),
                    ]),
            ])
            ->recordActions([
                Action::make('createLesson')
                    ->label('Tambah Materi')
                    ->icon('heroicon-o-plus-circle')
                    ->slideOver()
                    ->modalHeading(fn (CourseSection $record): string => 'Tambah Materi ke '.$record->title)
                    ->schema(fn (CourseSection $record): array => CourseLessonForm::getComponents([
                        'course_selectable' => false,
                        'course_id' => $this->getOwnerRecord()->getKey(),
                        'section_selectable' => false,
                        'section_id' => $record->getKey(),
                        'attachments_enabled' => false,
                    ]))
                    ->action(function (array $data, CourseSection $record): void {
                        CourseLesson::query()->create([
                            ...$data,
                            'course_id' => $this->getOwnerRecord()->getKey(),
                            'course_section_id' => $record->getKey(),
                        ]);
                    }),
                EditAction::make()
                    ->label('Edit')
                    ->slideOver()
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'course_id' => $this->getOwnerRecord()->getKey(),
                    ]),
                DeleteAction::make()
                    ->label('Hapus')
                    ->modalDescription('Modul akan dihapus. Materi yang sebelumnya berada di modul ini akan tetap ada, tetapi tidak lagi terhubung ke modul tersebut.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->sections()->count();
    }
}
