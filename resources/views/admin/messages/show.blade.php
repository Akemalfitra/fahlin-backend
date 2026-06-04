<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat User #{{ $userId }} | Fahlin Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0a0a0a] text-gray-200 font-sans h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-[#111] border-b border-gray-800 p-5 flex items-center justify-between shadow-lg">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.messages.index') }}" class="text-gray-400 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-white">User #{{ $userId }}</h2>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-xs text-gray-500 uppercase tracking-widest font-medium">Customer</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Chat Box -->
    <main class="flex-1 overflow-y-auto p-6 space-y-4" id="chat-box">
        @foreach($messages as $msg)
            <div class="flex {{ $msg->sender == 'admin' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[75%] lg:max-w-md">
                    <div class="px-4 py-3 rounded-2xl shadow-sm {{ $msg->sender == 'admin' ? 'bg-[#333] text-white rounded-tr-none' : 'bg-gray-800 text-gray-100 rounded-tl-none' }}">
                        <p class="text-sm leading-relaxed">{{ $msg->message }}</p>
                    </div>
                    <span class="text-[10px] text-gray-600 mt-1 block px-2 {{ $msg->sender == 'admin' ? 'text-right' : 'text-left' }}">
                        {{ $msg->created_at->format('H:i') }}
                    </span>
                </div>
            </div>
        @endforeach
    </main>

    <!-- Footer / Input Area -->
    <footer class="bg-[#111] border-t border-gray-800 p-6 shadow-2xl">
        <form action="{{ route('admin.messages.store') }}" method="POST" class="max-w-5xl mx-auto flex items-center space-x-4">
            @csrf
            <input type="hidden" name="user_id" value="{{ $userId }}">
            <div class="flex-1 relative">
                <input type="text" name="message" placeholder="Tulis balasan untuk User #{{ $userId }}..." required
                       class="w-full bg-[#1c1c1c] border border-gray-700 text-white rounded-full px-6 py-4 outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500 transition-all placeholder-gray-600">
            </div>
            <button type="submit" class="bg-white text-black font-bold px-8 py-4 rounded-full hover:bg-gray-200 transition-colors shadow-lg active:scale-95 duration-75">
                KIRIM
            </button>
        </form>
    </footer>

    <script>
        // Otomatis scroll ke bawah saat halaman dimuat
        const chatBox = document.getElementById('chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;
    </script>
</body>
</html>