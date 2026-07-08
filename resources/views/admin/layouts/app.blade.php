<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard') - Desa Getas</title>
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
                <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.bookings.*') ? 'bg-emerald-800' : '' }}">
                    <span>📋</span> Bookings
                </a>
                <a href="{{ route('admin.dusun.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.dusun.*') ? 'bg-emerald-800' : '' }}">
                    <span>🏘️</span> Dusun
                </a>
                <a href="{{ route('admin.tour-packages.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.tour-packages.*') ? 'bg-emerald-800' : '' }}">
                    <span>🎫</span> Paket Wisata
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
                <a href="{{ route('admin.village-profile.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-800 transition {{ request()->routeIs('admin.village-profile.*') ? 'bg-emerald-800' : '' }}">
                    <span>📄</span> Profil Desa
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
