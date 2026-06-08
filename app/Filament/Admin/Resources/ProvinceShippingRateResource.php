<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProvinceShippingRateResource\Pages;
use App\Models\ProvinceShippingRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProvinceShippingRateResource extends Resource
{
    protected static ?string $model = ProvinceShippingRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Master Ongkir Provinsi';

    protected static ?string $modelLabel = 'Ongkir Provinsi';

    protected static ?string $pluralModelLabel = 'Ongkir Provinsi';

    protected static ?string $navigationGroup = 'Pengaturan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Ongkir Provinsi')
                    ->schema([
                        Forms\Components\TextInput::make('province_name')
                            ->label('Nama Provinsi')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                        Forms\Components\TextInput::make('shipping_rate')
                            ->label('Tarif Pengiriman')
                            ->prefix('Rp')
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
                Tables\Columns\TextColumn::make('province_name')
                    ->label('Provinsi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_rate')
                    ->label('Tarif Pengiriman')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
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
            'index' => Pages\ListProvinceShippingRates::route('/'),
            'create' => Pages\CreateProvinceShippingRate::route('/create'),
            'edit' => Pages\EditProvinceShippingRate::route('/{record}/edit'),
        ];
    }
}
