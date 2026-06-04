<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MessageResource\Pages;
use App\Models\Message;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Messages';
    protected static ?string $navigationGroup = 'Customer Service';

    public static function table(Table $table): Table
    {
        return $table
            // Mengambil pesan terbaru unik per user_id agar tidak double di list
            ->query(    
                Message::query()
                    ->whereIn('id', function ($query) {
                        $query->selectRaw('max(id)')
                            ->from('messages')
                            ->groupBy('user_id');
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pelanggan')
                    ->placeholder('Tanpa Nama')
                    ->description(fn (Message $record): string => "ID: #{$record->user_id}")
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Pesan Terakhir')
                    ->limit(40)
                    // Info warna: Info (Biru) untuk pesan masuk, Gray untuk balasan admin
                    ->color(fn (Message $record) => $record->sender === 'admin' ? 'gray' : 'info')
                    ->icon(fn (Message $record) => $record->sender === 'admin' ? 'heroicon-m-arrow-uturn-left' : null),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terakhir Aktif')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                // Tombol Buka Chat
                Tables\Actions\Action::make('chat_room')
                    ->label('Buka Chat')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    // Memastikan nama user muncul di judul modal
                    ->modalHeading(fn (Message $record) => "Chat dengan " . ($record->user?->name ?? "User #{$record->user_id}"))
                    ->modalSubmitActionLabel('Kirim Balasan') 
                    ->modalWidth('lg')
                    ->form([
                        Forms\Components\Placeholder::make('history')
                            ->label('')
                            ->content(fn (Message $record) => new HtmlString(
                                view('filament.admin.chat-history', [
                                    'messages' => \App\Models\Message::where('user_id', $record->user_id)
                                        ->orderBy('created_at', 'asc')
                                        ->get()
                                ])->render()
                            )),
                        
                        Forms\Components\Textarea::make('reply_message')
                            ->label('Balasan Admin')
                            ->placeholder('Tulis pesan balasan...')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function (Message $record, array $data) {
                        // Mencari product_id terakhir dari percakapan ini agar admin tahu konteksnya
                        $lastProductId = \App\Models\Message::where('user_id', $record->user_id)
                            ->whereNotNull('product_id')
                            ->latest()
                            ->first()?->product_id;

                        \App\Models\Message::create([
                            'user_id' => $record->user_id,
                            'message' => $data['reply_message'],
                            'sender' => 'admin',
                            'product_id' => $lastProductId,
                        ]);

                        // Notifikasi sukses setelah membalas
                        \Filament\Notifications\Notification::make()
                            ->title('Pesan Terkirim')
                            ->success()
                            ->send();
                    }),
            ])
            ->actionsColumnLabel('Opsi')
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessages::route('/'),
        ];
    }
}