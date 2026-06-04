<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    // Mengganti ikon agar lebih sesuai dengan "Iklan/Promosi"
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Banner Promosi';

    protected static ?string $navigationGroup = 'Promosi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pengaturan Banner Promosi')
                    ->description('Kelola gambar promosi yang muncul di slide Home aplikasi Flutter.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Iklan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Promo Diskon Aksesoris 50%'),

                        Forms\Components\FileUpload::make('image_path')
                            ->label('Gambar Banner Promosi')
                            ->image()
                            ->directory('banners') // Folder: storage/app/public/banners
                            ->imageEditor() // Biar bisa crop gambar iklan
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Jika dimatikan, iklan tidak akan muncul di aplikasi.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Banner'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Promo')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
