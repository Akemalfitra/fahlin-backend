<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesan | Fahlin Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0a0a0a] text-gray-200 font-sans min-h-screen">
    <div class="max-w-6xl mx-auto p-8">
        <div class="flex justify-between items-center border-b border-gray-800 pb-6 mb-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-white">Daftar Chat Masuk</h1>
            <span class="bg-gray-800 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider text-gray-400">
                Messages Console
            </span>
        </div>

        <div class="grid gap-4">
            @if($chats->isEmpty())
                <div class="bg-[#161616] border border-gray-800 p-12 text-center rounded-2xl">
                    <p class="text-gray-500">Belum ada pesan masuk dari user.</p>
                </div>
            @else
                @foreach($chats as $chat)
                    <div class="group bg-[#161616] border border-gray-800 hover:border-gray-600 transition-all duration-300 rounded-2xl overflow-hidden shadow-sm hover:shadow-md">
                        <a href="{{ route('admin.messages.show', $chat->user_id) }}" class="flex items-center justify-between p-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center text-white font-bold">
                                    {{ $chat->user_id }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">User ID #{{ $chat->user_id }}</h3>
                                    <p class="text-sm text-gray-500">Klik untuk melihat riwayat percakapan</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 group-hover:text-white transition-colors">Buka Chat</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</body>
</html>