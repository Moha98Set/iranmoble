<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use App\Models\BankAccount;
use App\Rules\CardNumber;
use App\Rules\Iban;
use App\Support\Digits;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات حساب')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان حساب')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('حساب اصلی')
                            ->helperText('نامی که خودتان این حساب را با آن می‌شناسید.'),

                        TextInput::make('bank_name')
                            ->label('نام بانک')
                            ->required()
                            ->maxLength(255)
                            ->datalist([
                                'ملی', 'ملت', 'صادرات', 'تجارت', 'سپه', 'پاسارگاد',
                                'پارسیان', 'سامان', 'کشاورزی', 'مسکن', 'رفاه',
                                'اقتصاد نوین', 'شهر', 'دی', 'سینا', 'آینده',
                                'گردشگری', 'کارآفرین', 'پست بانک', 'قرض‌الحسنه مهر',
                            ]),

                        TextInput::make('account_holder')
                            ->label('صاحب حساب')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('bank_branch')
                            ->label('شعبه بانک')
                            ->maxLength(255)
                            ->helperText('شعبه‌ی بانک، نه شعبه‌ی شرکت.'),
                    ]),

                Section::make('شماره‌ها')
                    ->description('حداقل یکی از شماره حساب، شماره کارت یا شبا لازم است.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('account_number')
                            ->label('شماره حساب')
                            ->maxLength(50),

                        TextInput::make('card_number')
                            ->label('شماره کارت')
                            ->maxLength(25)
                            ->placeholder('۶۰۳۷۹۹۱۱۲۳۴۵۶۷۸۹')
                            ->helperText('۱۶ رقم. با فاصله یا خط تیره هم پذیرفته می‌شود.')
                            ->rules([new CardNumber])
                            ->dehydrateStateUsing(fn (?string $state): ?string => blank($state) ? null : Digits::onlyNumbers($state))
                            ->formatStateUsing(fn (?string $state): ?string => blank($state) ? null : implode('-', str_split($state, 4)))
                            ->unique(
                                table: BankAccount::class,
                                column: 'card_number',
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'),
                            ),

                        TextInput::make('iban')
                            ->label('شماره شبا')
                            ->maxLength(40)
                            ->prefix('IR')
                            ->helperText('۲۴ رقم بعد از IR.')
                            ->rules([new Iban])
                            ->dehydrateStateUsing(fn (?string $state): ?string => blank($state) ? null : Iban::normalise($state))
                            ->unique(
                                table: BankAccount::class,
                                column: 'iban',
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'),
                            )
                            ->columnSpanFull(),
                    ]),

                Section::make('سایر')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('فعال')
                            ->default(true)
                            ->helperText('حساب غیرفعال در فهرست‌های انتخاب حساب ظاهر نمی‌شود.'),

                        Textarea::make('description')
                            ->label('توضیحات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
