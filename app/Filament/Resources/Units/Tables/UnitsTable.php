<?php

namespace App\Filament\Resources\Units\Tables;

use App\Models\Branch;
use App\Models\Building;
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

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Eager load both levels: without this the branch column fires a
            // query per row.
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('building.branch'))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('building.branch.name')
                    ->label('شعبه')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('building.name')
                    ->label('ساختمان')
                    ->sortable()
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('unit_number')
                    ->label('واحد')
                    ->sortable()
                    ->searchable()
                    ->badge(),

                TextColumn::make('floor')
                    ->label('طبقه')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('bedrooms')
                    ->label('خواب')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('capacity')
                    ->label('ظرفیت')
                    ->sortable()
                    ->placeholder('—')
                    ->formatStateUsing(fn (?int $state): string => filled($state) ? "{$state} نفر" : '—')
                    ->toggleable(),

                TextColumn::make('base_price')
                    ->label('قیمت پایه')
                    ->sortable()
                    ->placeholder('—')
                    ->formatStateUsing(fn (?int $state): string => filled($state) ? number_format($state).' تومان' : '—'),

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
                Group::make('building.name')
                    ->label('ساختمان')
                    ->collapsible(),

                Group::make('building.branch.name')
                    ->label('شعبه')
                    ->collapsible(),
            ])
            ->filters([
                SelectFilter::make('branch')
                    ->label('شعبه')
                    ->options(fn (): array => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $q, $branchId): Builder => $q->whereHas('building', fn (Builder $b) => $b->where('branch_id', $branchId))
                    )),

                SelectFilter::make('building_id')
                    ->label('ساختمان')
                    ->options(fn (): array => Building::query()
                        ->with('branch')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Building $b): array => [
                            $b->getKey() => $b->name.' — '.($b->branch?->name ?? '?'),
                        ])
                        ->all())
                    ->searchable(),

                TernaryFilter::make('is_active')
                    ->label('وضعیت')
                    ->placeholder('همه')
                    ->trueLabel('فعال')
                    ->falseLabel('غیرفعال'),

                TrashedFilter::make()
                    ->label('حذف‌شده‌ها'),
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
            ->emptyStateHeading('هنوز واحدی ثبت نشده')
            ->emptyStateDescription('برای شروع، یک واحد جدید ثبت کنید. شعبه و ساختمان را می‌توانید از داخل همان فرم بسازید.');
    }
}
