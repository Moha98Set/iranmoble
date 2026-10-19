<?php

namespace App\Filament\Exports;

use App\Filament\Resources\Activities\Tables\ActivitiesTable;
use App\Models\Activity;
use App\Support\Jalali;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ActivityExporter extends Exporter
{
    protected static ?string $model = Activity::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('شناسه'),

            ExportColumn::make('created_at')
                ->label('تاریخ')
                ->formatStateUsing(fn ($state): ?string => Jalali::format($state, Jalali::DATETIME_FORMAT)),

            ExportColumn::make('event')
                ->label('رویداد')
                ->formatStateUsing(fn (?string $state): string => ActivitiesTable::EVENTS[$state] ?? (string) $state),

            ExportColumn::make('subject_type')
                ->label('نوع موضوع')
                ->formatStateUsing(fn (?string $state): string => class_basename((string) $state)),

            ExportColumn::make('subject_id')
                ->label('شناسه موضوع'),

            ExportColumn::make('causer.name')
                ->label('انجام‌دهنده'),

            ExportColumn::make('causer.email')
                ->label('ایمیل انجام‌دهنده'),

            ExportColumn::make('properties')
                ->label('تغییرات')
                ->formatStateUsing(fn ($state): string => self::describeChanges($state)),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $rows = number_format($export->successful_rows);

        $body = "خروجی گزارش فعالیت‌ها آماده شد: {$rows} ردیف.";

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' ردیف ناموفق بود.';
        }

        return $body;
    }

    /**
     * Flattens the properties JSON into "field: old -> new" lines so the
     * spreadsheet is readable without having to parse JSON by hand.
     */
    protected static function describeChanges(mixed $properties): string
    {
        $properties = is_string($properties) ? json_decode($properties, true) : (array) $properties;

        if (! is_array($properties)) {
            return '';
        }

        $new = $properties['attributes'] ?? [];
        $old = $properties['old'] ?? [];

        $lines = [];

        foreach (array_keys($new + $old) as $field) {
            $before = $old[$field] ?? '—';
            $after = $new[$field] ?? '—';

            $before = is_array($before) ? json_encode($before, JSON_UNESCAPED_UNICODE) : $before;
            $after = is_array($after) ? json_encode($after, JSON_UNESCAPED_UNICODE) : $after;

            $lines[] = "{$field}: {$before} ← {$after}";
        }

        return implode("\n", $lines);
    }
}
