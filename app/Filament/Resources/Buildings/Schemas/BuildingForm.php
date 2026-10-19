<?php

namespace App\Filament\Resources\Buildings\Schemas;

use App\Models\Branch;
use App\Models\Building;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class BuildingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات ساختمان')
                    ->columns(2)
                    ->schema([
                        Select::make('branch_id')
                            ->label('شعبه')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->label('نام شعبه')->required()->maxLength(255),
                                TextInput::make('code')->label('کد')->maxLength(50),
                                TextInput::make('phone')->label('تلفن')->tel()->maxLength(50),
                                Textarea::make('address')->label('آدرس')->rows(2)->columnSpanFull(),
                            ])
                            ->createOptionUsing(fn (array $data): int => Branch::create($data)->getKey())
                            ->createOptionModalHeading('ثبت شعبه جدید'),

                        TextInput::make('name')
                            ->label('نام ساختمان')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مریم')
                            ->rules([
                                fn (callable $get, ?Building $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record): void {
                                    $exists = Building::query()
                                        ->where('branch_id', $get('branch_id'))
                                        ->where('name', $value)
                                        ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey()))
                                        ->exists();

                                    if ($exists) {
                                        $fail('در این شعبه ساختمانی با این نام ثبت شده است.');
                                    }
                                },
                            ]),

                        TextInput::make('floors_count')
                            ->label('تعداد طبقات')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(255),

                        Toggle::make('is_active')
                            ->label('فعال')
                            ->default(true),

                        Textarea::make('address')
                            ->label('آدرس')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
