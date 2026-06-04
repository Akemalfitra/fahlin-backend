<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            // Statistik Total Produk
            Stat::make('Total Produk', \App\Models\Product::count())
                ->description('Semua produk di katalog')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            // Statistik Total User
            Stat::make('Total Pelanggan', User::count())
                ->description('User yang terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            // STATISTIK USER ONLINE (REAL-TIME)
            Stat::make('User Online', User::where('last_seen_at', '>=', now()->subMinutes(5))->count())
                ->description('Aktif dalam 5 menit terakhir')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->chart([7, 2, 10, 3, 15, 4, 17]) // Contoh grafik kecil
                ->color('success'),
        ];
    }
}