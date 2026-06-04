<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SiteSettingResource\Pages;
use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Toko';

    protected static ?string $modelLabel = 'Pengaturan Toko';

    protected static ?string $pluralModelLabel = 'Pengaturan Toko';

    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Kontak dan Aplikasi')
                    ->description('Pengaturan ini dipakai di website Fahlin Store bagian tombol WhatsApp, Instagram, dan install aplikasi.')
                    ->schema([
                        Forms\Components\TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp Admin')
                            ->helperText('Gunakan format internasional tanpa tanda +. Contoh: 6281234567890.')
                            ->required()
                            ->maxLength(30),

                        Forms\Components\TextInput::make('instagram_url')
                            ->label('Link Instagram')
                            ->helperText('Isi dengan link profil Instagram. Jika kosong, tombol Instagram tidak ditampilkan di website.')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.instagram.com/username'),

                        Forms\Components\TextInput::make('app_install_url')
                            ->label('Link Install Aplikasi')
                            ->helperText('Isi dengan link Google Play, APK, Drive, atau halaman download. Jika kosong, tombol install akan mengarah ke WhatsApp admin.')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://...'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Media Utama Website')
                    ->description('Media ini tampil sebagai background hero di halaman depan. Bisa memakai foto atau video.')
                    ->schema([
                        Forms\Components\Select::make('hero_media_type')
                            ->label('Jenis Media')
                            ->options([
                                'image' => 'Foto',
                                'video' => 'Video',
                            ])
                            ->default('image')
                            ->required()
                            ->live()
                            ->native(false),

                        Forms\Components\FileUpload::make('hero_media_path')
                            ->label('File Background')
                            ->directory('hero-media')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'video/mp4',
                                'video/x-m4v',
                                'video/webm',
                                'video/ogg',
                                'application/mp4',
                            ])
                            ->maxSize(204800)
                            ->helperText('Upload JPG, PNG, WebP, MP4, M4V, WebM, atau OGG. Batas maksimal 200MB.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('whatsapp_number')
                    ->label('WhatsApp Admin')
                    ->searchable(),

                Tables\Columns\TextColumn::make('app_install_url')
                    ->label('Link Install')
                    ->limit(45)
                    ->placeholder('Belum diisi'),

                Tables\Columns\TextColumn::make('instagram_url')
                    ->label('Instagram')
                    ->limit(45)
                    ->placeholder('Belum diisi'),

                Tables\Columns\TextColumn::make('hero_media_type')
                    ->label('Media Hero')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'video' ? 'Video' : 'Foto'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteSettings::route('/'),
            'create' => Pages\CreateSiteSetting::route('/create'),
            'edit' => Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
