<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivitiesTable
{
    /**
     * Event names as stored by the package, mapped to Persian.
     */
    public const EVENTS = [
        'created' => 'ایجاد',
        'updated' => 'ویرایش',
        'deleted' => 'حذف',
        'restored' => 'بازیابی',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->jalaliDateTime()
                    ->sortable(),

                TextColumn::make('event')
                    ->label('رویداد')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::EVENTS[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('subject_type')
                    ->label('موضوع')
                    ->formatStateUsing(fn (?string $state): string => class_basename((string) $state))
                    ->description(fn (?object $record): ?string => $record?->subject_id ? "#{$record->subject_id}" : null),

                TextColumn::make('causer.name')
                    ->label('کاربر')
                    ->placeholder('سیستم')
                    ->searchable(),

                TextColumn::make('properties')
                    ->label('تغییرات')
                    ->formatStateUsing(fn ($state): string => self::summarise($state))
                    ->wrap()
                    ->limit(60),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('رویداد')
                    ->options(self::EVENTS),

                SelectFilter::make('subject_type')
                    ->label('نوع موضوع')
                    ->options(fn (): array => \App\Models\Activity::query()
                        ->distinct()
                        ->pluck('subject_type', 'subject_type')
                        ->filter()
                        ->map(fn ($type): string => class_basename((string) $type))
                        ->all()),

                Filter::make('created_at')
                    ->label('بازه تاریخ')
                    ->schema([
                        DatePicker::make('from')->label('از تاریخ')->jalali(),
                        DatePicker::make('until')->label('تا تاریخ')->jalali(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make()->label('مشاهده'),
            ])
            // No create, edit, delete or bulk actions: the log is append-only.
            ->toolbarActions([]);
    }

    /**
     * One-line summary of which fields changed, for the list view.
     */
    protected static function summarise(mixed $properties): string
    {
        $properties = is_string($properties) ? json_decode($properties, true) : $properties;

        if (! is_array($properties)) {
            return '—';
        }

        $changed = array_keys($properties['attributes'] ?? []);

        return $changed === [] ? '—' : implode('، ', $changed);
    }
}
