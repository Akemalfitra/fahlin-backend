<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Penjualan';

    protected static ?string $description = 'Total penjualan 12 bulan terakhir';

    protected static ?int $sort = 2;

    protected static string $color = 'success';

    protected function getData(): array
    {
        $labels = [];
        $sales = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $labels[] = $date->translatedFormat('M Y');
            $sales[] = (int) Order::query()
                ->where('status', '!=', 'Dibatalkan')
                ->whereBetween('created_at', [$start, $end])
                ->sum('total');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Penjualan',
                    'data' => $sales,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.35)',
                    'borderColor' => 'rgb(22, 163, 74)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
