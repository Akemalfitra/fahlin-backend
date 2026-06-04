<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    // Ikon di sidebar admin
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Menggunakan Section agar form terlihat rapi dan berkelompok
                Forms\Components\Section::make('Informasi Produk')
                    ->description('Lengkapi detail produk aksesoris Fahlin Store di sini.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Luna Pearl Dream Strap'),

                        Forms\Components\TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->prefix('Rp') // Menambahkan prefix Rp otomatis
                            ->required(),

                        Forms\Components\Repeater::make('productImages')
                            ->label('Foto dan Pilihan Produk')
                            ->helperText('Setiap foto bisa mewakili warna atau model yang dapat dipilih user.')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->schema([
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Foto')
                                    ->image()
                                    ->directory('products')
                                    ->imageEditor()
                                    ->required(),

                                Forms\Components\TextInput::make('label')
                                    ->label('Nama Pilihan')
                                    ->placeholder('Contoh: Pink, Hitam, Model A')
                                    ->required()
                                    ->maxLength(120),

                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi Pilihan')
                                    ->placeholder('Contoh: Warna pink pastel dengan gantungan bunga.')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Pilihan produk')
                            ->columnSpanFull()
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('stock')
                            ->label('Stok Barang')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])->columns(2), // Membuat input nama dan harga berdampingan
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Menampilkan foto kecil di tabel
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->circular(), // Membuat foto berbentuk bulat

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable() // Bisa dicari berdasarkan nama
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->badge() // Membuat tampilan angka stok seperti badge
                    ->color(fn (string $state): string => match (true) {
                        $state <= 5 => 'danger', // Merah jika stok sedikit
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Tambah aksi hapus satuan
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
