<?php

namespace App\Filament\Resources\Units;

use App\Filament\Resources\Units\Pages\CreateUnit;
use App\Filament\Resources\Units\Pages\EditUnit;
use App\Filament\Resources\Units\Pages\ListUnits;
use App\Filament\Resources\Units\Schemas\UnitForm;
use App\Filament\Resources\Units\Tables\UnitsTable;
use App\Models\Unit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static string|UnitEnum|null $navigationGroup = 'مدیریت املاک';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return 'واحدها';
    }

    public static function getModelLabel(): string
    {
        return 'واحد';
    }

    public static function getPluralModelLabel(): string
    {
        return 'واحدها';
    }

    /**
     * What a staff member says out loud: "واحد ۴ ساختمان مریم شعبه پاسداران".
     */
    public static function getRecordTitle(?object $record): ?string
    {
        return $record?->full_title;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['unit_number', 'building.name', 'building.branch.name'];
    }

    public static function getGlobalSearchResultDetails(object $record): array
    {
        return [
            'ساختمان' => $record->building?->name,
            'شعبه' => $record->building?->branch?->name,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return UnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnits::route('/'),
            'create' => CreateUnit::route('/create'),
            'edit' => EditUnit::route('/{record}/edit'),
        ];
    }
}
