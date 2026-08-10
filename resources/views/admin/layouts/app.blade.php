<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard') - Desa Getas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex">
        @auth
        <aside class="w-64 bg-emerald-900 text-white flex flex-col">
            <div class="p-5 border-b border-emerald-800">
                <h1 class="text-lg font-bold">Desa Getas</h1>
                <p class="text-xs text-emerald-300">Admin Panel</p>
            </div>
            <nav class="flex-1 p-4 space-y-1 text-sm overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-800' : '' }}">
                    <span>📊</span> Dashboard
                </a>

                <div class="pt-3 pb-1.5 px-3 text-[10px] font-bold uppercase tracking-widest text-emerald-300/80">Kasir / POS Terminal</div>
                <a href="{{ route('admin.pos.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium transition-all duration-200 {{ request()->routeIs('admin.pos.index') ? 'bg-emerald-800 text-white shadow-sm ring-1 ring-emerald-700/50' : 'text-emerald-100 hover:bg-emerald-800/60 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" class="text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span>Kasir POS</span>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-700/60 text-emerald-200">LIVE</span>
                </a>
                <a href="{{ route('admin.pos.products.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all duration-200 {{ request()->routeIs('admin.pos.products.*') ? 'bg-emerald-800 text-white shadow-sm ring-1 ring-emerald-700/50' : 'text-emerald-100 hover:bg-emerald-800/60 hover:text-white' }}">
                    <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" class="text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Produk POS</span>
                </a>
                <a href="{{ route('admin.pos.transactions.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all duration-200 {{ request()->routeIs('admin.pos.transactions.*') ? 'bg-emerald-800 text-white shadow-sm ring-1 ring-emerald-700/50' : 'text-emerald-100 hover:bg-emerald-800/60 hover:text-white' }}">
                    <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" class="text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span>Riwayat POS</span>
                </a>

                <div class="pt-2 pb-1 text-[11px] font-bold uppercase tracking-wider text-emerald-400">Pariwisata & Desa</div>
                <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.bookings.*') ? 'bg-emerald-800' : '' }}">
                    <span>📋</span> Bookings
                </a>
                <a href="{{ route('admin.dusun.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.dusun.*') ? 'bg-emerald-800' : '' }}">
                    <span>🏘️</span> Dusun
                </a>
                <a href="{{ route('admin.paket-wisata.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.paket-wisata.*') ? 'bg-emerald-800' : '' }}">
                    <span>🎫</span> Paket Wisata
                </a>
                <a href="{{ route('admin.add-ons.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.add-ons.*') ? 'bg-emerald-800' : '' }}">
                    <span>🍱</span> Add-On
                </a>
                <a href="{{ route('admin.booking-sessions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.booking-sessions.*') ? 'bg-emerald-800' : '' }}">
                    <span>📅</span> Sesi Booking
                </a>
                <a href="{{ route('admin.umkm-products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.umkm-products.*') ? 'bg-emerald-800' : '' }}">
                    <span>🛍️</span> UMKM
                </a>
                <a href="{{ route('admin.budaya.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.budaya.*') ? 'bg-emerald-800' : '' }}">
                    <span>🎭</span> Budaya
                </a>
                <a href="{{ route('admin.village-stats.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.village-stats.*') ? 'bg-emerald-800' : '' }}">
                    <span>📊</span> Statistik
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.settings.*') ? 'bg-emerald-800' : '' }}">
                    <span>⚙️</span> Pengaturan
                </a>
                <a href="{{ route('admin.fonnte.device') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.fonnte.*') ? 'bg-emerald-800' : '' }}">
                    <span>📱</span> Status Fonnte
                </a>
                @if(auth()->user()->role === 'superadmin')
                <hr class="border-emerald-700 my-2">
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.users.*') ? 'bg-emerald-800' : '' }}">
                    <span>👥</span> Kelola Admin
                </a>
                @endif
            </nav>
            <div class="p-4 border-t border-emerald-800">
                <div class="text-xs text-emerald-300 mb-2">{{ auth()->user()->nama }}</div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-xs text-emerald-400 hover:text-white transition">Logout</button>
                </form>
            </div>
        </aside>
        @endauth

        <main class="flex-1 @auth p-6 @endauth">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
