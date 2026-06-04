@php
    $adminWhatsapp = $settings?->whatsapp_number ?: '6281234567890';
    $whatsappMessage = rawurlencode('Halo admin Fahlin Store, saya ingin memesan produk.');
    $installMessage = rawurlencode('Halo admin Fahlin Store, saya ingin install aplikasi Fahlin Store. Bisa kirim link aplikasinya?');
    $appInstallUrl = $settings?->app_install_url ?: 'https://wa.me/' . $adminWhatsapp . '?text=' . $installMessage;
    $instagramUrl = $settings?->instagram_url;

    $fallbackProducts = collect([
        [
            'name' => 'Luna Pearl Dream Strap',
            'price' => 89000,
            'description' => 'Strap elegan dengan detail mutiara untuk gaya harian yang rapi.',
            'stock' => 12,
            'image' => 'products/01KR3ND2N69J3R6SBXETFSJN82.jpeg',
        ],
        [
            'name' => 'Classic Grey Phone Charm',
            'price' => 59000,
            'description' => 'Charm minimalis bernuansa abu-abu dengan aksen metal clean.',
            'stock' => 18,
            'image' => 'products/01KQT39R3E2EG5EDX7X4MMPPJR.jpeg',
        ],
        [
            'name' => 'Everyday Soft Case',
            'price' => 79000,
            'description' => 'Case lembut, ringan, dan nyaman dipakai untuk aktivitas padat.',
            'stock' => 9,
            'image' => 'products/01KQT3715VZX7FG15PNGK4673F.jpeg',
        ],
        [
            'name' => 'Fahlin Mini Organizer',
            'price' => 69000,
            'description' => 'Pouch ringkas untuk menyimpan aksesori kecil agar tetap tertata.',
            'stock' => 15,
            'image' => 'products/01KQG43S3B2NBM9Q1ZQN0EJMQ5.jpeg',
        ],
    ]);

    $displayProducts = $products->isNotEmpty() ? $products : $fallbackProducts;
    $heroBanner = $banners->first();
    $heroMediaPath = $settings?->hero_media_path;
    $heroMediaExtension = strtolower(pathinfo($heroMediaPath ?: '', PATHINFO_EXTENSION));
    $heroMediaType = in_array($heroMediaExtension, ['mp4', 'm4v', 'webm', 'ogg', 'ogv'], true)
        ? 'video'
        : ($settings?->hero_media_type === 'video' ? 'video' : 'image');
    $heroFallbackImage = $heroBanner ? asset('storage/' . $heroBanner->image_path) : asset('storage/banners/01KR3NRRH184S1WF5849QSZ386.jpg');
    $heroMediaUrl = $heroMediaPath ? asset('storage/' . ltrim($heroMediaPath, '/')) : $heroFallbackImage;

    $imageUrl = function ($path) {
        return $path ? asset('storage/' . ltrim($path, '/')) : 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80';
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fahlin Store</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
    <style>
        :root {
            --ink: #161616;
            --muted: #6b6b6b;
            --line: #dedede;
            --soft: #f5f5f5;
            --paper: #ffffff;
            --charcoal: #2d2d2d;
            --silver: #b9b9b9;
            --shadow: 0 24px 80px rgba(0, 0, 0, .12);
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--ink);
            background: #eeeeee;
        }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }

        .page { overflow: hidden; }
        .shell { width: min(1160px, calc(100% - 32px)); margin: 0 auto; }

        .nav {
            position: fixed;
            inset: 14px 0 auto;
            z-index: 20;
            pointer-events: none;
        }
        .nav-inner {
            pointer-events: auto;
            width: min(1160px, calc(100% - 32px));
            margin: 0 auto;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: rgba(255, 255, 255, .82);
            border: 1px solid rgba(255, 255, 255, .68);
            box-shadow: 0 14px 45px rgba(0, 0, 0, .11);
            backdrop-filter: blur(18px);
            border-radius: 8px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            letter-spacing: 0;
        }
        .brand-mark {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            color: #fff;
            background: linear-gradient(135deg, #151515, #737373);
            font-weight: 800;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 14px;
            color: #333;
        }
        .nav-actions { display: flex; gap: 8px; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #fff;
            color: #1f1f1f;
            font-weight: 700;
            font-size: 14px;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
            white-space: nowrap;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(0, 0, 0, .12); }
        .btn.primary { background: #1e1e1e; color: #fff; border-color: #1e1e1e; }
        .btn.ghost { background: rgba(255, 255, 255, .72); }
        .btn.full { width: 100%; }

        .hero {
            position: relative;
            min-height: 96vh;
            padding: 112px 0 48px;
            display: grid;
            align-items: end;
            color: #fff;
            background: #242424;
            isolation: isolate;
        }
        .hero-media,
        .hero-media img,
        .hero-media video,
        .hero::after {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }
        .hero-media {
            z-index: -2;
            overflow: hidden;
        }
        .hero-media img,
        .hero-media video {
            object-fit: cover;
        }
        .hero::after {
            content: "";
            z-index: -1;
            background: linear-gradient(90deg, rgba(0, 0, 0, .86), rgba(0, 0, 0, .5) 46%, rgba(0, 0, 0, .2));
        }
        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(320px, .75fr);
            gap: 34px;
            align-items: end;
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 999px;
            color: #ededed;
            background: rgba(255, 255, 255, .08);
            font-size: 13px;
            font-weight: 700;
        }
        .dot { width: 8px; height: 8px; border-radius: 999px; background: #d9d9d9; }
        h1 {
            margin: 20px 0 18px;
            font-size: clamp(44px, 7vw, 92px);
            line-height: .96;
            letter-spacing: 0;
        }
        .hero-copy {
            max-width: 650px;
            color: #e4e4e4;
            font-size: clamp(16px, 2vw, 20px);
            line-height: 1.7;
            margin: 0 0 28px;
        }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .hero-panel {
            padding: 18px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .88);
            color: var(--ink);
            box-shadow: var(--shadow);
        }
        .hero-panel h2 { margin: 0 0 12px; font-size: 18px; }
        .metric-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .metric {
            padding: 14px 12px;
            border-radius: 8px;
            background: #f1f1f1;
            border: 1px solid #e2e2e2;
        }
        .metric strong { display: block; font-size: 22px; }
        .metric span { color: var(--muted); font-size: 12px; }

        .section { padding: 82px 0; background: #f7f7f7; }
        .section.alt { background: #e8e8e8; }
        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 24px;
            margin-bottom: 30px;
        }
        .section-title { margin: 0; font-size: clamp(30px, 4vw, 48px); line-height: 1.05; }
        .section-text { max-width: 520px; color: var(--muted); line-height: 1.7; margin: 0; }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }
        .product-card {
            overflow: hidden;
            border-radius: 8px;
            background: var(--paper);
            border: 1px solid #e0e0e0;
            box-shadow: 0 14px 40px rgba(0, 0, 0, .07);
        }
        .product-media {
            position: relative;
            aspect-ratio: 1 / 1.06;
            background: #d8d8d8;
            overflow: hidden;
        }
        .product-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .35s ease;
        }
        .product-card:hover img { transform: scale(1.05); }
        .badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 6px 9px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .9);
            font-size: 12px;
            font-weight: 800;
        }
        .product-body { padding: 16px; }
        .product-body h3 { margin: 0 0 8px; font-size: 17px; line-height: 1.3; }
        .product-body p { min-height: 50px; margin: 0 0 14px; color: var(--muted); font-size: 14px; line-height: 1.55; }
        .price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }
        .price { font-size: 18px; font-weight: 800; }
        .stock { color: var(--muted); font-size: 13px; }

        .steps {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
        }
        .step {
            padding: 24px;
            border-radius: 8px;
            background: #fff;
            border: 1px solid #dedede;
        }
        .step-number {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: #222;
            color: #fff;
            font-weight: 800;
            margin-bottom: 18px;
        }
        .step h3 { margin: 0 0 8px; font-size: 19px; }
        .step p { margin: 0; color: var(--muted); line-height: 1.7; }

        .install-band {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(280px, .55fr);
            gap: 26px;
            align-items: center;
            padding: 34px;
            border-radius: 8px;
            background: #222;
            color: #fff;
            box-shadow: var(--shadow);
        }
        .install-band h2 { margin: 0 0 12px; font-size: clamp(28px, 4vw, 44px); line-height: 1.05; }
        .install-band p { margin: 0; color: #d4d4d4; line-height: 1.7; }
        .install-actions { display: grid; gap: 10px; }
        .install-actions .btn { border-color: rgba(255,255,255,.2); }

        .footer {
            padding: 28px 0;
            background: #151515;
            color: #d8d8d8;
        }
        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            font-size: 14px;
        }

        @media (max-width: 1180px) {
            .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 920px) {
            .nav-links { display: none; }
            .hero { min-height: 780px; padding: 104px 0 40px; }
            .hero::after { background: linear-gradient(180deg, rgba(0, 0, 0, .7), rgba(0, 0, 0, .55)); }
            .hero-grid, .install-band { grid-template-columns: 1fr; }
            .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .steps { grid-template-columns: 1fr; }
        }

        @media (max-width: 560px) {
            .shell, .nav-inner { width: min(100% - 24px, 1160px); }
            .nav-actions .ghost { display: none; }
            .hero { min-height: 720px; }
            .hero-actions { display: grid; }
            .hero-panel { padding: 14px; }
            .metric-grid { grid-template-columns: 1fr; }
            .section { padding: 58px 0; }
            .section-head { display: block; }
            .section-text { margin-top: 12px; }
            .product-grid { grid-template-columns: 1fr; }
            .install-band { padding: 24px; }
            .footer-inner { display: block; }
            .footer-inner span { display: block; margin-top: 8px; }
        }
    </style>
</head>
<body>
    <div class="page">
        <nav class="nav" aria-label="Navigasi utama">
            <div class="nav-inner">
                <a href="#" class="brand" aria-label="Fahlin Store">
                    <span class="brand-mark">F</span>
                    <span>Fahlin Store</span>
                </a>
                <div class="nav-links">
                    <a href="#produk">Produk</a>
                    <a href="#cara-pesan">Cara Pesan</a>
                    <a href="#install-app">Aplikasi</a>
                    @if ($instagramUrl)
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer">Instagram</a>
                    @endif
                </div>
                <div class="nav-actions">
                    @if ($instagramUrl)
                        <a class="btn ghost" href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer">Instagram</a>
                    @endif
                    <a class="btn ghost" href="https://wa.me/{{ $adminWhatsapp }}?text={{ $whatsappMessage }}">WhatsApp</a>
                    <a class="btn primary" href="#produk">Belanja</a>
                </div>
            </div>
        </nav>

        <header class="hero">
            <div class="hero-media" aria-hidden="true">
                @if ($heroMediaType === 'video' && $heroMediaPath)
                    <video src="{{ $heroMediaUrl }}" autoplay muted loop playsinline preload="metadata"></video>
                @else
                    <img src="{{ $heroMediaUrl }}" alt="">
                @endif
            </div>
            <div class="shell hero-grid">
                <div>
                    <span class="eyebrow"><span class="dot"></span> Koleksi aksesori pilihan Fahlin</span>
                    <h1>Fahlin Store</h1>
                    <p class="hero-copy">
                        Temukan aksesori cantik, minimalis, dan mudah dipadukan untuk gaya harian. Pilih produk favorit, lalu lanjutkan pemesanan lewat aplikasi atau langsung chat admin.
                    </p>
                    <div class="hero-actions">
                        <a class="btn primary" href="#produk">Lihat Produk</a>
                        <a class="btn ghost" href="https://wa.me/{{ $adminWhatsapp }}?text={{ $whatsappMessage }}">Chat Admin</a>
                    </div>
                </div>

                <aside class="hero-panel" aria-label="Ringkasan Fahlin Store">
                    <h2>Belanja cepat dan rapi</h2>
                    <div class="metric-grid">
                        <div class="metric">
                            <strong>{{ $displayProducts->count() }}+</strong>
                            <span>Produk tampil</span>
                        </div>
                        <div class="metric">
                            <strong>24J</strong>
                            <span>Respon admin</span>
                        </div>
                        <div class="metric">
                            <strong>COD</strong>
                            <span>Bisa diskusi</span>
                        </div>
                    </div>
                </aside>
            </div>
        </header>

        <main>
            <section id="produk" class="section">
                <div class="shell">
                    <div class="section-head">
                        <div>
                            <h2 class="section-title">Produk Pilihan</h2>
                        </div>
                        <p class="section-text">
                            Katalog ini otomatis memakai data produk dari admin. Kalau data belum lengkap, halaman tetap menampilkan koleksi contoh agar storefront tetap terlihat hidup.
                        </p>
                    </div>

                    <div class="product-grid">
                        @foreach ($displayProducts as $product)
                            @php
                                $name = is_array($product) ? $product['name'] : $product->name;
                                $price = is_array($product) ? $product['price'] : $product->price;
                                $description = is_array($product) ? $product['description'] : $product->description;
                                $shortDescription = \Illuminate\Support\Str::limit($description ?: 'Produk Fahlin Store dengan tampilan clean dan mudah dipadukan.', 118);
                                $stock = is_array($product) ? $product['stock'] : $product->stock;
                                $image = is_array($product) ? $product['image'] : $product->image;
                                $productMessage = rawurlencode('Halo admin Fahlin Store, saya ingin pesan ' . $name . '.');
                            @endphp
                            <article class="product-card">
                                <div class="product-media">
                                    <img src="{{ $imageUrl($image) }}" alt="{{ $name }}">
                                    <span class="badge">{{ $stock > 0 ? 'Ready Stock' : 'Pre-order' }}</span>
                                </div>
                                <div class="product-body">
                                    <h3>{{ $name }}</h3>
                                    <p>{{ $shortDescription }}</p>
                                    <div class="price-row">
                                        <span class="price">Rp{{ number_format($price, 0, ',', '.') }}</span>
                                        <span class="stock">Stok {{ $stock }}</span>
                                    </div>
                                    <a class="btn primary full" href="https://wa.me/{{ $adminWhatsapp }}?text={{ $productMessage }}">Pesan Produk</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="cara-pesan" class="section alt">
                <div class="shell">
                    <div class="section-head">
                        <h2 class="section-title">Cara Memesan</h2>
                        <p class="section-text">Alur dibuat singkat supaya pembeli bisa langsung lanjut ke aplikasi atau WhatsApp tanpa bingung.</p>
                    </div>

                    <div class="steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <h3>Pilih produk</h3>
                            <p>Lihat katalog, cek harga, stok, dan foto produk yang paling sesuai.</p>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <h3>Install aplikasi</h3>
                            <p>Gunakan aplikasi Fahlin Store untuk pengalaman belanja yang lebih lengkap.</p>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <h3>Chat admin</h3>
                            <p>Kalau ingin pemesanan cepat, tombol produk langsung membuka WhatsApp admin.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="install-app" class="section">
                <div class="shell">
                    <div class="install-band">
                        <div>
                            <h2>Siap pesan dari Fahlin Store?</h2>
                            <p>
                                Lanjutkan melalui aplikasi Fahlin Store saat file aplikasinya sudah tersedia, atau chat admin untuk konfirmasi produk, stok, dan metode pembayaran.
                            </p>
                        </div>
                        <div class="install-actions">
                            <a class="btn" href="{{ $appInstallUrl }}">Install Aplikasi</a>
                            @if ($instagramUrl)
                                <a class="btn" href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer">Instagram Fahlin Store</a>
                            @endif
                            <a class="btn primary" href="https://wa.me/{{ $adminWhatsapp }}?text={{ $whatsappMessage }}">Chat Admin WhatsApp</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="footer">
            <div class="shell footer-inner">
                <strong>Fahlin Store</strong>
                <span>
                    Katalog aksesori dengan warna abu-abu yang clean, modern, dan mudah dipakai.
                    @if ($instagramUrl)
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer">Instagram</a>
                    @endif
                </span>
            </div>
        </footer>
    </div>
</body>
</html>
