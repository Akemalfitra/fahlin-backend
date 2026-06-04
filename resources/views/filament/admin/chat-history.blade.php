<div class="flex flex-col space-y-4 overflow-y-auto max-h-[500px] p-4 bg-gray-50 rounded-xl border border-gray-200 dark:bg-gray-900 dark:border-gray-800">
    @foreach($messages as $msg)
        <div class="flex {{ $msg->sender === 'admin' ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[85%] px-4 py-2 rounded-2xl shadow-sm 
                {{ $msg->sender === 'admin' 
                    ? 'bg-amber-500 text-white rounded-tr-none' 
                    : 'bg-white text-gray-800 border border-gray-100 rounded-tl-none dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700' }}">
                
                {{-- Nama Pengirim (Hanya tampil jika pengirimnya bukan admin) --}}
                @if($msg->sender !== 'admin')
                    <p class="text-[10px] font-bold mb-1 text-amber-600 dark:text-amber-400">
                        {{ $msg->user?->name ?? 'Pelanggan' }}
                    </p>
                @endif

                {{-- Info Produk (Ala Shopee Card) --}}
                @if($msg->product)
                    <div class="mb-2 p-2 bg-black/5 rounded-lg flex items-center gap-3 border border-black/5">
                        {{-- Thumbnail Produk --}}
                        <img src="{{ asset('storage/' . $msg->product->image) }}" 
                             alt="Product" 
                             class="w-10 h-10 rounded object-cover shadow-sm">
                        
                        <div class="flex-1 overflow-hidden">
                            <p class="text-[9px] font-bold uppercase tracking-wider opacity-60">Produk Terkait</p>
                            <p class="text-xs font-semibold truncate">{{ $msg->product->name }}</p>
                            <p class="text-[10px] text-amber-600 dark:text-amber-400 font-bold">
                                Rp {{ number_format($msg->product->price, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Isi Pesan --}}
                <p class="text-sm leading-relaxed">{{ $msg->message }}</p>
                
                {{-- Waktu --}}
                <p class="text-[9px] mt-1 opacity-70 text-right font-medium">
                    {{ $msg->created_at->format('H:i') }}
                </p>
            </div>
        </div>
    @endforeach
</div>