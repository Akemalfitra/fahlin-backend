<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VoucherAdResource\Pages;
use App\Models\VoucherAd;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VoucherAdResource extends Resource
{
    protected static ?string $model = VoucherAd::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Iklan Voucher';

    protected static ?string $navigationGroup = 'Promosi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Foto Iklan Klaim Voucher')
                    ->description('Gambar aktif terbaru akan muncul sebagai kotak iklan saat user baru login di aplikasi mobile.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Iklan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Klaim Voucher Spesial Hari Ini'),

                        Forms\Components\FileUpload::make('image_path')
                            ->label('Foto Iklan')
                            ->image()
                            ->directory('voucher-ads')
                            ->imageEditor()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Hanya iklan aktif terbaru yang ditampilkan di mobile.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Foto'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Iklan')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVoucherAds::route('/'),
            'create' => Pages\CreateVoucherAd::route('/create'),
            'edit' => Pages\EditVoucherAd::route('/{record}/edit'),
        ];
    }
}
