<?php

namespace App\Filament\Resources\Activities\Schemas;

use App\Filament\Resources\Activities\Tables\ActivitiesTable;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('شناسه رویداد')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاریخ و ساعت')
                            ->jalaliDateTime(),

                        TextEntry::make('event')
                            ->label('رویداد')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => ActivitiesTable::EVENTS[$state] ?? (string) $state),

                        TextEntry::make('subject_type')
                            ->label('نوع موضوع')
                            ->formatStateUsing(fn (?string $state): string => class_basename((string) $state)),

                        TextEntry::make('subject_id')
                            ->label('شناسه موضوع')
                            ->placeholder('—'),

                        TextEntry::make('causer.name')
                            ->label('انجام‌دهنده')
                            ->placeholder('سیستم'),

                        TextEntry::make('causer.email')
                            ->label('ایمیل انجام‌دهنده')
                            ->placeholder('—'),
                    ]),

                Section::make('تغییرات')
                    ->description('مقدار پیشین و مقدار جدید هر فیلدی که تغییر کرده است.')
                    ->schema([
                        RepeatableEntry::make('changes')
                            ->label('')
                            ->state(fn ($record): array => self::changes($record))
                            ->columns(3)
                            ->schema([
                                TextEntry::make('field')->label('فیلد'),
                                TextEntry::make('old')->label('مقدار پیشین')->placeholder('—'),
                                TextEntry::make('new')->label('مقدار جدید')->placeholder('—'),
                            ]),
                    ]),
            ]);
    }

    /**
     * Flattens the stored properties into one row per changed field, so the
     * view shows a readable before/after table instead of raw JSON.
     *
     * @return list<array{field: string, old: ?string, new: ?string}>
     */
    protected static function changes(object $record): array
    {
        $properties = $record->properties;
        $properties = is_string($properties) ? json_decode($properties, true) : (array) $properties;

        $new = $properties['attributes'] ?? [];
        $old = $properties['old'] ?? [];

        $rows = [];

        foreach (array_keys($new + $old) as $field) {
            $rows[] = [
                'field' => (string) $field,
                'old' => self::stringify($old[$field] ?? null),
                'new' => self::stringify($new[$field] ?? null),
            ];
        }

        return $rows;
    }

    protected static function stringify(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'بله' : 'خیر';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }
}
