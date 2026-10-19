<?php

namespace App\Filament\Resources\Branches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Counts come from the query, not from loading every relation.
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount(['buildings', 'units']))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('نام شعبه')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('code')
                    ->label('کد')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('buildings_count')
                    ->label('ساختمان')
                    ->badge()
                    ->sortable(),

                TextColumn::make('units_count')
                    ->label('واحد')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('تلفن')
                    ->placeholder('—')
                    ->toggleable(),

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
            ->filters([
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
            ->emptyStateHeading('هنوز شعبه‌ای ثبت نشده')
            ->emptyStateDescription('شعبه بالاترین سطح است: هر شعبه ساختمان دارد و هر ساختمان واحد.');
    }
}
