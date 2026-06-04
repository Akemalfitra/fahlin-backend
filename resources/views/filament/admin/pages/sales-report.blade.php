<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($this->getSummaryCards() as $card)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $card['value'] }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $card['description'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Penjualan Harian</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">14 hari terakhir</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3 text-right">Pesanan</th>
                                <th class="px-5 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($this->getDailyRows() as $row)
                                <tr>
                                    <td class="px-5 py-3 text-gray-700 dark:text-gray-200">{{ $row['period'] }}</td>
                                    <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-200">{{ $row['orders'] }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-gray-950 dark:text-white">{{ $row['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Penjualan Bulanan</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tahun {{ now()->year }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Bulan</th>
                                <th class="px-5 py-3 text-right">Pesanan</th>
                                <th class="px-5 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($this->getMonthlyRows() as $row)
                                <tr>
                                    <td class="px-5 py-3 text-gray-700 dark:text-gray-200">{{ $row['period'] }}</td>
                                    <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-200">{{ $row['orders'] }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-gray-950 dark:text-white">{{ $row['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Penjualan Tahunan</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">5 tahun terakhir</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Tahun</th>
                                <th class="px-5 py-3 text-right">Pesanan</th>
                                <th class="px-5 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($this->getYearlyRows() as $row)
                                <tr>
                                    <td class="px-5 py-3 text-gray-700 dark:text-gray-200">{{ $row['period'] }}</td>
                                    <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-200">{{ $row['orders'] }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-gray-950 dark:text-white">{{ $row['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            Data dihitung dari pesanan selain status Dibatalkan.
        </p>
    </div>
</x-filament-panels::page>
