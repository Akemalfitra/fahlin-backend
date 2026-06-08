<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ShippingSettingResource\Pages;
use App\Models\ShippingSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingSettingResource extends Resource
{
    protected static ?string $model = ShippingSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Pengaturan Ongkir';

    protected static ?string $modelLabel = 'Pengaturan Ongkir';

    protected static ?string $pluralModelLabel = 'Pengaturan Ongkir';

    protected static ?string $navigationGroup = 'Pengaturan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Lokasi Toko')
                    ->schema([
                        Forms\Components\TextInput::make('store_latitude')
                            ->label('Latitude Toko')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('store_longitude')
                            ->label('Longitude Toko')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Formula Ongkir')
                    ->schema([
                        Forms\Components\TextInput::make('base_fee')
                            ->label('Biaya Dasar')
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('fee_per_km')
                            ->label('Biaya Per KM')
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('free_distance')
                            ->label('Batas Jarak Gratis')
                            ->suffix('KM')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('max_radius')
                            ->label('Radius Maksimum')
                            ->suffix('KM')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('base_fee')
                    ->label('Biaya Dasar')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('fee_per_km')
                    ->label('Per KM')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('free_distance')
                    ->label('Jarak Gratis')
                    ->suffix(' KM'),
                Tables\Columns\TextColumn::make('max_radius')
                    ->label('Radius')
                    ->suffix(' KM'),
                Tables\Columns\TextColumn::make('store_latitude')
                    ->label('Latitude'),
                Tables\Columns\TextColumn::make('store_longitude')
                    ->label('Longitude'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingSettings::route('/'),
            'create' => Pages\CreateShippingSetting::route('/create'),
            'edit' => Pages\EditShippingSetting::route('/{record}/edit'),
        ];
    }
}
