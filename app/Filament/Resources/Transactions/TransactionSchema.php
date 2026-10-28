<?php

namespace App\Filament\Resources\Transactions;

use App\Models\BankAccount;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * Income and expense use the same fields and the same columns, so the shape
 * lives here once. Each resource passes its own wording.
 */
class TransactionSchema
{
    public const RECEIPT_DISK = 'local';

    public const RECEIPT_DIRECTORY = 'receipts';

    public static function form(Schema $schema, string $amountLabel, string $subjectPlaceholder): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات اصلی')
                    ->columns(2)
                    ->schema([
                        TextInput::make('amount')
                            ->label($amountLabel)
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('تومان'),

                        DatePicker::make('occurred_at')
                            ->label('تاریخ')
                            ->required()
                            ->default(now())
                            ->jalali(),

                        Select::make('bank_account_id')
                            ->label('حساب بانکی')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => BankAccount::query()
                                ->where('is_active', true)
                                ->orderBy('title')
                                ->get()
                                ->mapWithKeys(fn (BankAccount $a): array => [
                                    $a->getKey() => $a->title.' — '.$a->bank_name,
                                ])
                                ->all()),

                        TextInput::make('subject')
                            ->label('بابت')
                            ->required()
                            ->maxLength(255)
                            ->placeholder($subjectPlaceholder),
                    ]),

                Section::make('فیش و توضیحات')
                    ->schema([
                        FileUpload::make('receipt_path')
                            ->label('فیش')
                            ->disk(self::RECEIPT_DISK)
                            ->directory(self::RECEIPT_DIRECTORY)
                            ->visibility('private')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                            ->maxSize(5120)
                            ->helperText('اختیاری. تصویر یا PDF، حداکثر ۵ مگابایت.')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('توضیحات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table, string $amountLabel, string $amountColor): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['bankAccount', 'creator']))
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('تاریخ')
                    ->jalaliDate()
                    ->sortable(),

                TextColumn::make('subject')
                    ->label('بابت')
                    ->searchable()
                    ->wrap()
                    ->weight('medium'),

                TextColumn::make('amount')
                    ->label($amountLabel)
                    ->sortable()
                    ->color($amountColor)
                    ->weight('medium')
                    ->formatStateUsing(fn (?int $state): string => number_format((int) $state).' تومان')
                    ->summarize(
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('مجموع')
                            ->formatStateUsing(fn ($state): string => number_format((int) $state).' تومان')
                    ),

                TextColumn::make('bankAccount.title')
                    ->label('حساب')
                    ->searchable()
                    ->description(fn ($record): ?string => $record->bankAccount?->bank_name)
                    ->toggleable(),

                TextColumn::make('creator.name')
                    ->label('ثبت‌کننده')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('receipt_path')
                    ->label('فیش')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus-small')
                    ->getStateUsing(fn ($record): bool => filled($record->receipt_path)),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('bank_account_id')
                    ->label('حساب بانکی')
                    ->options(fn (): array => BankAccount::query()->orderBy('title')->pluck('title', 'id')->all())
                    ->searchable(),

                Filter::make('occurred_at')
                    ->label('بازه تاریخ')
                    ->schema([
                        DatePicker::make('from')->label('از تاریخ')->jalali(),
                        DatePicker::make('until')->label('تا تاریخ')->jalali(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $d): Builder => $q->whereDate('occurred_at', '>=', $d))
                        ->when($data['until'] ?? null, fn (Builder $q, $d): Builder => $q->whereDate('occurred_at', '<=', $d))),

                TernaryFilter::make('receipt_path')
                    ->label('فیش')
                    ->placeholder('همه')
                    ->trueLabel('دارای فیش')
                    ->falseLabel('بدون فیش')
                    ->queries(
                        true: fn (Builder $q): Builder => $q->whereNotNull('receipt_path'),
                        false: fn (Builder $q): Builder => $q->whereNull('receipt_path'),
                        blank: fn (Builder $q): Builder => $q,
                    ),

                TrashedFilter::make()->label('حذف‌شده‌ها'),
            ])
            ->recordActions([
                /*
                 * Receipts live outside public/, so they are streamed through
                 * the panel where the session is already authenticated.
                 */
                Action::make('receipt')
                    ->label('فیش')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn ($record): bool => filled($record->receipt_path)
                        && Storage::disk(self::RECEIPT_DISK)->exists($record->receipt_path))
                    ->action(fn ($record) => Storage::disk(self::RECEIPT_DISK)->download(
                        $record->receipt_path,
                        'receipt-'.$record->getKey().'.'.pathinfo($record->receipt_path, PATHINFO_EXTENSION),
                    )),

                EditAction::make()->label('ویرایش'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
