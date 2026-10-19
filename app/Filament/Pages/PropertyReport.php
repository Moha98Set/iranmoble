<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * Decomposition tree over branch -> building -> unit, with counts and the
 * summed base price at every level.
 *
 * This is a Page, not a Resource: there is no single model behind it and
 * nothing to create or edit.
 */
class PropertyReport extends Page
{
    protected string $view = 'filament.pages.property-report';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'مدیریت املاک';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return 'گزارش املاک';
    }

    public function getTitle(): string
    {
        return 'گزارش املاک';
    }

    public function getHeading(): string
    {
        return 'گزارش املاک';
    }

    public function getSubheading(): ?string
    {
        return 'ساختار شعبه، ساختمان و واحد به همراه تعداد و مجموع قیمت پایه. تنها واحدهای فعال شمرده می‌شوند.';
    }

    /**
     * Whole tree in a fixed number of queries. Walking the relations in the
     * view instead would fire one query per branch and per building.
     *
     * @return Collection<int, Branch>
     */
    public function getBranches(): Collection
    {
        /*
         * Qualified on purpose: the branch-level aggregate goes through
         * hasManyThrough, which joins buildings to units and both tables have
         * an is_active column.
         */
        $activeUnits = fn (Builder $query): Builder => $query->where('units.is_active', true);

        return Branch::query()
            ->withCount('buildings')
            ->withCount(['units as units_count' => $activeUnits])
            ->withSum(['units as units_sum' => $activeUnits], 'base_price')
            ->with([
                'buildings' => fn ($query) => $query
                    ->withCount(['units as units_count' => $activeUnits])
                    ->withSum(['units as units_sum' => $activeUnits], 'base_price')
                    ->with(['units' => fn ($q) => $q->where('units.is_active', true)->orderByRaw('LENGTH(unit_number), unit_number')])
                    ->orderBy('name'),
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array{branches: int, buildings: int, units: int, total: int}
     */
    public function getTotals(): array
    {
        $branches = $this->getBranchesCached();

        return [
            'branches' => $branches->count(),
            'buildings' => (int) $branches->sum('buildings_count'),
            'units' => (int) $branches->sum('units_count'),
            'total' => (int) $branches->sum('units_sum'),
        ];
    }

    protected ?Collection $cachedBranches = null;

    public function getBranchesCached(): Collection
    {
        return $this->cachedBranches ??= $this->getBranches();
    }
}
