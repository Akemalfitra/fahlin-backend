<?php

namespace App\Filament\Admin\Pages;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class SalesReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.admin.pages.sales-report';

    public function getTitle(): string
    {
        return 'Laporan Penjualan';
    }

    public function getSummaryCards(): array
    {
        $today = now();

        return [
            [
                'label' => 'Penjualan Hari Ini',
                'value' => $this->formatRupiah($this->sumSales($today->copy()->startOfDay(), $today->copy()->endOfDay())),
                'description' => $this->countOrders($today->copy()->startOfDay(), $today->copy()->endOfDay()) . ' pesanan',
            ],
            [
                'label' => 'Penjualan Bulan Ini',
                'value' => $this->formatRupiah($this->sumSales($today->copy()->startOfMonth(), $today->copy()->endOfMonth())),
                'description' => $this->countOrders($today->copy()->startOfMonth(), $today->copy()->endOfMonth()) . ' pesanan',
            ],
            [
                'label' => 'Penjualan Tahun Ini',
                'value' => $this->formatRupiah($this->sumSales($today->copy()->startOfYear(), $today->copy()->endOfYear())),
                'description' => $this->countOrders($today->copy()->startOfYear(), $today->copy()->endOfYear()) . ' pesanan',
            ],
        ];
    }

    public function getDailyRows(): array
    {
        $rows = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $rows[] = $this->makeRow(
                $date->translatedFormat('d M Y'),
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            );
        }

        return $rows;
    }

    public function getMonthlyRows(): array
    {
        $rows = [];
        $year = now()->year;

        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::create($year, $month, 1);
            $rows[] = $this->makeRow(
                $date->translatedFormat('F Y'),
                $date->copy()->startOfMonth(),
                $date->copy()->endOfMonth(),
            );
        }

        return $rows;
    }

    public function getYearlyRows(): array
    {
        $rows = [];
        $currentYear = now()->year;

        for ($year = $currentYear - 4; $year <= $currentYear; $year++) {
            $date = Carbon::create($year, 1, 1);
            $rows[] = $this->makeRow(
                (string) $year,
                $date->copy()->startOfYear(),
                $date->copy()->endOfYear(),
            );
        }

        return $rows;
    }

    private function makeRow(string $period, Carbon $start, Carbon $end): array
    {
        $total = $this->sumSales($start, $end);
        $orders = $this->countOrders($start, $end);

        return [
            'period' => $period,
            'orders' => $orders,
            'total' => $this->formatRupiah($total),
        ];
    }

    private function sumSales(Carbon $start, Carbon $end): int
    {
        return (int) $this->salesQuery()
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');
    }

    private function countOrders(Carbon $start, Carbon $end): int
    {
        return $this->salesQuery()
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private function salesQuery(): Builder
    {
        return Order::query()
            ->where('status', '!=', 'Dibatalkan');
    }

    private function formatRupiah(int $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
