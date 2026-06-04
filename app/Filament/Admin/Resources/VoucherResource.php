<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VoucherResource\Pages;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Voucher';

    protected static ?string $navigationGroup = 'Promosi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Voucher')
                    ->description('Voucher aktif akan muncul di aplikasi dan bisa diklaim pengguna.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Nama Voucher')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Diskon Akhir Pekan'),

                        Forms\Components\TextInput::make('code')
                            ->label('Kode Voucher')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Contoh: WEEKEND50')
                            ->dehydrateStateUsing(fn (?string $state): ?string => $state ? strtoupper($state) : null),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Aturan Diskon')
                    ->schema([
                        Forms\Components\Select::make('discount_type')
                            ->label('Tipe Diskon')
                            ->options([
                                'fixed' => 'Nominal Rupiah',
                                'percent' => 'Persentase',
                            ])
                            ->default('fixed')
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('discount_value')
                            ->label('Nilai Diskon')
                            ->numeric()
                            ->required()
                            ->prefix(fn (Forms\Get $get): string => $get('discount_type') === 'fixed' ? 'Rp' : '')
                            ->suffix(fn (Forms\Get $get): string => $get('discount_type') === 'percent' ? '%' : ''),

                        Forms\Components\TextInput::make('min_purchase')
                            ->label('Minimum Belanja')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('max_discount')
                            ->label('Maksimal Diskon')
                            ->helperText('Isi untuk membatasi diskon persentase. Kosongkan jika tidak ada batas.')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Ketersediaan')
                    ->schema([
                        Forms\Components\TextInput::make('quota')
                            ->label('Kuota Klaim')
                            ->helperText('Kosongkan jika voucher tidak dibatasi kuota.')
                            ->numeric()
                            ->minValue(1)
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Mulai Berlaku')
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Berakhir')
                            ->seconds(false)
                            ->afterOrEqual('starts_at'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Voucher')
                    ->description(fn (Voucher $record): string => $record->code)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Diskon')
                    ->formatStateUsing(fn (Voucher $record): string => $record->discount_type === 'percent'
                        ? rtrim(rtrim((string) $record->discount_value, '0'), '.') . '%'
                        : 'Rp ' . number_format((float) $record->discount_value, 0, ',', '.')),

                Tables\Columns\TextColumn::make('min_purchase')
                    ->label('Min. Belanja')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('claimed_count')
                    ->label('Diklaim')
                    ->formatStateUsing(fn (Voucher $record): string => $record->quota === null
                        ? "{$record->claimed_count} / Tanpa batas"
                        : "{$record->claimed_count} / {$record->quota}"),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Berakhir')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Tidak dibatasi'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                Tables\Filters\Filter::make('available')
                    ->label('Masih Bisa Diklaim')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('is_active', true)
                        ->where(function (Builder $query) {
                            $query->whereNull('expires_at')
                                ->orWhere('expires_at', '>=', now());
                        })
                        ->where(function (Builder $query) {
                            $query->whereNull('quota')
                                ->orWhereColumn('claimed_count', '<', 'quota');
                        })),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}
