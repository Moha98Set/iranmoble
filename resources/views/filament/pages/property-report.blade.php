@php
    $branches = $this->getBranchesCached();
    $totals = $this->getTotals();
@endphp

<x-filament-panels::page>
    {{-- Summary tiles --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['label' => 'شعبه', 'value' => number_format($totals['branches']), 'suffix' => null],
            ['label' => 'ساختمان', 'value' => number_format($totals['buildings']), 'suffix' => null],
            ['label' => 'واحد فعال', 'value' => number_format($totals['units']), 'suffix' => null],
            ['label' => 'مجموع قیمت پایه', 'value' => number_format($totals['total']), 'suffix' => 'تومان'],
        ] as $tile)
            <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $tile['label'] }}</div>
                <div class="mt-1 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $tile['value'] }}
                    @if ($tile['suffix'])
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">{{ $tile['suffix'] }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Decomposition tree --}}
    <div class="fi-section mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        @forelse ($branches as $branch)
            {{-- Branches start open so the overall shape is visible at a glance. --}}
            <details open class="border-b border-gray-200 last:border-b-0 dark:border-white/10">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 bg-gray-50 px-4 py-3 hover:bg-gray-100 dark:bg-white/5 dark:hover:bg-white/10">
                    <span class="flex items-center gap-2 font-semibold text-gray-950 dark:text-white">
                        <x-filament::icon icon="heroicon-m-chevron-left" class="h-4 w-4 shrink-0 text-gray-400 transition-transform [details[open]>summary_&]:-rotate-90" />
                        شعبه {{ $branch->name }}
                        @unless ($branch->is_active)
                            <span class="rounded bg-gray-200 px-1.5 py-0.5 text-xs font-normal text-gray-600 dark:bg-white/10 dark:text-gray-300">غیرفعال</span>
                        @endunless
                    </span>
                    <span class="flex shrink-0 items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ number_format($branch->buildings_count) }} ساختمان</span>
                        <span>{{ number_format($branch->units_count) }} واحد</span>
                        <span class="font-medium text-gray-950 dark:text-white">{{ number_format((int) $branch->units_sum) }} تومان</span>
                    </span>
                </summary>

                @forelse ($branch->buildings as $building)
                    <details class="border-t border-gray-100 dark:border-white/5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 py-2.5 pe-4 ps-10 hover:bg-gray-50 dark:hover:bg-white/5">
                            <span class="flex items-center gap-2 text-gray-800 dark:text-gray-200">
                                <x-filament::icon icon="heroicon-m-chevron-left" class="h-3.5 w-3.5 shrink-0 text-gray-400 transition-transform [details[open]>summary_&]:-rotate-90" />
                                ساختمان {{ $building->name }}
                            </span>
                            <span class="flex shrink-0 items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                <span>{{ number_format($building->units_count) }} واحد</span>
                                <span class="font-medium text-gray-950 dark:text-white">{{ number_format((int) $building->units_sum) }} تومان</span>
                            </span>
                        </summary>

                        @forelse ($building->units as $unit)
                            <div class="flex items-center justify-between gap-3 border-t border-gray-100 py-2 pe-4 ps-16 text-sm dark:border-white/5">
                                <span class="text-gray-700 dark:text-gray-300">
                                    واحد {{ $unit->unit_number }}
                                    <span class="text-gray-400 dark:text-gray-500">
                                        @if (filled($unit->floor)) · طبقه {{ $unit->floor }} @endif
                                        @if (filled($unit->bedrooms)) · {{ $unit->bedrooms }} خواب @endif
                                        @if (filled($unit->capacity)) · {{ $unit->capacity }} نفر @endif
                                    </span>
                                </span>
                                <span class="shrink-0 text-gray-950 dark:text-white">
                                    @if (filled($unit->base_price))
                                        {{ number_format($unit->base_price) }} تومان
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">قیمت ثبت نشده</span>
                                    @endif
                                </span>
                            </div>
                        @empty
                            <div class="border-t border-gray-100 py-2 pe-4 ps-16 text-sm text-gray-400 dark:border-white/5 dark:text-gray-500">
                                واحد فعالی ندارد.
                            </div>
                        @endforelse
                    </details>
                @empty
                    <div class="border-t border-gray-100 py-2.5 pe-4 ps-10 text-sm text-gray-400 dark:border-white/5 dark:text-gray-500">
                        ساختمانی ندارد.
                    </div>
                @endforelse
            </details>
        @empty
            <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                هنوز شعبه‌ای ثبت نشده است.
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
