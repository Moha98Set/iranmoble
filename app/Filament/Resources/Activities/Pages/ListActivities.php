<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Exports\ActivityExporter;
use App\Filament\Resources\Activities\ActivityResource;
use App\Models\Activity;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    /**
     * No CreateAction: rows are written by the model observers only.
     * Export is gated on its own permission, not on read access.
     */
    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('خروجی اکسل')
                ->exporter(ActivityExporter::class)
                ->visible(fn (): bool => auth()->user()?->can('export', Activity::class) ?? false),
        ];
    }
}
