<?php

namespace App\Filament\Resources\Units\Schemas;

use App\Models\Branch;
use App\Models\Building;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('موقعیت')
                    ->description('شعبه و ساختمانی که این واحد در آن قرار دارد.')
                    ->columns(2)
                    ->schema([
                        /*
                         * branch_id is not a column on units — it only exists
                         * to narrow the building list. It is filled from the
                         * saved record when editing.
                         */
                        Select::make('branch_id')
                            ->label('شعبه')
                            ->options(fn (): array => Branch::query()
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(fn (Select $component, $state) => $component
                                ->getContainer()
                                ->getComponent('building_id')
                                ?->state(null))
                            ->formatStateUsing(fn ($state, ?\App\Models\Unit $record) => $state ?? $record?->building?->branch_id)
                            ->createOptionForm([
                                TextInput::make('name')->label('نام شعبه')->required()->maxLength(255),
                                TextInput::make('code')->label('کد')->maxLength(50),
                                TextInput::make('phone')->label('تلفن')->tel()->maxLength(50),
                                Textarea::make('address')->label('آدرس')->rows(2)->columnSpanFull(),
                            ])
                            ->createOptionUsing(fn (array $data): int => Branch::create($data)->getKey())
                            ->createOptionModalHeading('ثبت شعبه جدید'),

                        Select::make('building_id')
                            ->key('building_id')
                            ->label('ساختمان')
                            ->options(fn (callable $get): array => filled($get('branch_id'))
                                ? Building::query()
                                    ->where('branch_id', $get('branch_id'))
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all()
                                : [])
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('ابتدا شعبه را انتخاب کنید.')
                            ->createOptionForm([
                                TextInput::make('name')->label('نام ساختمان')->required()->maxLength(255),
                                TextInput::make('floors_count')->label('تعداد طبقات')->numeric()->minValue(1)->maxValue(255),
                                Textarea::make('address')->label('آدرس')->rows(2)->columnSpanFull(),
                            ])
                            // The branch comes from the field above, so the
                            // modal never asks for it again.
                            ->createOptionUsing(fn (array $data, callable $get): int => Building::create([
                                ...$data,
                                'branch_id' => $get('branch_id'),
                            ])->getKey())
                            ->createOptionModalHeading('ثبت ساختمان جدید')
                            ->disabled(fn (callable $get): bool => blank($get('branch_id'))),

                        TextInput::make('unit_number')
                            ->label('شماره واحد')
                            ->required()
                            ->maxLength(50)
                            ->helperText('مثل ۴ یا ۴ب یا همکف.')
                            ->rules([
                                fn (callable $get, ?\App\Models\Unit $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record): void {
                                    $exists = \App\Models\Unit::query()
                                        ->where('building_id', $get('building_id'))
                                        ->where('unit_number', $value)
                                        ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey()))
                                        ->exists();

                                    if ($exists) {
                                        $fail('در این ساختمان واحدی با این شماره ثبت شده است.');
                                    }
                                },
                            ]),

                        TextInput::make('floor')
                            ->label('طبقه')
                            ->numeric()
                            ->minValue(-5)
                            ->maxValue(200),
                    ]),

                Section::make('مشخصات')
                    ->columns(2)
                    ->schema([
                        TextInput::make('bedrooms')
                            ->label('اتاق خواب')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(20),

                        TextInput::make('capacity')
                            ->label('ظرفیت')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50)
                            ->suffix('نفر'),

                        TextInput::make('base_price')
                            ->label('قیمت پایه')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('تومان')
                            ->helperText('قیمت هر شب، به تومان.')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('توضیحات')
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('فعال')
                            ->default(true)
                            ->helperText('واحد غیرفعال از سرویس خارج است (بازسازی یا تعمیر).')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
