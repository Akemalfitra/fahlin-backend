<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Pesanan';

    protected static ?string $modelLabel = 'Pesanan';

    protected static ?string $pluralModelLabel = 'Pesanan';

    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Status Pesanan')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Nomor Pesanan')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Menunggu Pembayaran' => 'Menunggu Pembayaran',
                                'COD - Menunggu Pengiriman' => 'COD - Menunggu Pengiriman',
                                'Diproses' => 'Diproses',
                                'Dikirim' => 'Dikirim',
                                'Selesai' => 'Selesai',
                                'Dibatalkan' => 'Dibatalkan',
                            ])
                            ->required(),
                        Forms\Components\DateTimePicker::make('delivery_datetime')
                            ->label('Jadwal Antar'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Tujuan Pengantaran')
                    ->schema([
                        Forms\Components\TextInput::make('recipient_name')
                            ->label('Penerima')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('recipient_phone')
                            ->label('No. HP')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('address_label')
                            ->label('Label Alamat')
                            ->required()
                            ->maxLength(120),
                        Forms\Components\Textarea::make('address_detail')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('delivery_latitude')
                            ->label('Latitude')
                            ->numeric(),
                        Forms\Components\TextInput::make('delivery_longitude')
                            ->label('Longitude')
                            ->numeric(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest())
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Penerima')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('address_detail')
                    ->label('Tujuan Antar')
                    ->limit(42)
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Selesai' => 'success',
                        'Dibatalkan' => 'danger',
                        'Dikirim' => 'info',
                        'COD - Menunggu Pengiriman' => 'info',
                        default => 'warning',
                    }),
                Tables\Columns\IconColumn::make('has_location')
                    ->label('Maps')
                    ->state(fn (Order $record): bool => $record->hasDeliveryLocation())
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('map')
                    ->label('Lihat Maps')
                    ->icon('heroicon-o-map-pin')
                    ->visible(fn (Order $record): bool => $record->hasDeliveryLocation())
                    ->modalHeading(fn (Order $record): string => 'Tujuan Antar ' . $record->order_number)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (Order $record) => view('filament.admin.order-map', ['order' => $record])),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
