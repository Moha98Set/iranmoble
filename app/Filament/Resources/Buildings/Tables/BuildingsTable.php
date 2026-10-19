<?php

namespace App\Filament\Resources\Buildings\Tables;

use App\Models\Branch;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BuildingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('branch')->withCount('units'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('branch.name')
                    ->label('شعبه')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('نام ساختمان')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('units_count')
                    ->label('واحد')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('floors_count')
                    ->label('طبقات')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('address')
                    ->label('آدرس')
                    ->placeholder('—')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Group::make('branch.name')
                    ->label('شعبه')
                    ->collapsible(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('شعبه')
                    ->options(fn (): array => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),

                TernaryFilter::make('is_active')
                    ->label('وضعیت')
                    ->placeholder('همه')
                    ->trueLabel('فعال')
                    ->falseLabel('غیرفعال'),

                TrashedFilter::make()->label('حذف‌شده‌ها'),
            ])
            ->recordActions([
                EditAction::make()->label('ویرایش'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('هنوز ساختمانی ثبت نشده')
            ->emptyStateDescription('هر ساختمان به یک شعبه تعلق دارد و واحدهای آن زیرمجموعه‌اش هستند.');
    }
}
