@php
    $lat = (float) $order->delivery_latitude;
    $lng = (float) $order->delivery_longitude;
    $delta = 0.01;
    $bbox = ($lng - $delta) . ',' . ($lat - $delta) . ',' . ($lng + $delta) . ',' . ($lat + $delta);
    $embedUrl = 'https://www.openstreetmap.org/export/embed.html?bbox=' . urlencode($bbox) . '&layer=mapnik&marker=' . urlencode($lat . ',' . $lng);
    $openUrl = 'https://www.openstreetmap.org/?mlat=' . urlencode($lat) . '&mlon=' . urlencode($lng) . '#map=17/' . urlencode($lat) . '/' . urlencode($lng);
@endphp

<div class="space-y-4">
    <div>
        <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $order->recipient_name ?: 'Penerima belum diisi' }}</p>
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $order->address_label }} - {{ $order->address_detail }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $lat }}, {{ $lng }}</p>
    </div>

    <iframe
        title="Peta tujuan antar {{ $order->order_number }}"
        src="{{ $embedUrl }}"
        class="w-full rounded-lg border border-gray-200 dark:border-gray-700"
        style="height: 360px;"
        loading="lazy">
    </iframe>

    <a
        href="{{ $openUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">
        Buka di OpenStreetMap
    </a>
</div>
