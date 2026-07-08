<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard') - Desa Getas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Custom Scrollbar for Sidebar */
        .sidebar-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }
        .sidebar-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }
        .sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="bg-[#f8f9fa] text-gray-800 antialiased selection:bg-[#0d3b2e] selection:text-white overflow-hidden">
    <div class="h-screen flex w-full">
        @auth
        <aside class="w-64 h-full bg-[#0d3b2e] text-white flex flex-col shrink-0 border-r border-[#0d3b2e]">
            <div class="px-6 py-8 flex items-center gap-3 shrink-0">
                <img src="{{ asset('assets/Logo_Gardu_V2.png') }}" alt="Logo Desa Getas" class="w-9 h-9 object-contain" style="max-width: 36px; max-height: 36px; width: auto; height: auto;">
                <div>
                    <h1 class="text-base font-semibold tracking-tight text-white/95">Desa Getas</h1>
                    <p class="text-[11px] font-medium text-white/60 uppercase tracking-widest mt-0.5">Admin Panel</p>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-6 overflow-y-auto pb-6 sidebar-scrollbar">
                
                <div>
                    <h3 class="px-3 text-[10px] font-bold text-white/40 uppercase tracking-wider mb-2">Utama</h3>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.bookings.*') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                            Bookings
                        </a>
                        <a href="{{ route('admin.booking-sessions.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.booking-sessions.*') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                            Sesi Booking
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="px-3 text-[10px] font-bold text-white/40 uppercase tracking-wider mb-2">Konten Desa</h3>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dusun.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.dusun.*') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"/><path d="M15 5.764v15"/><path d="M9 3.236v15"/></svg>
                            Dusun
                        </a>
                        <a href="{{ route('admin.tour-packages.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.tour-packages.*') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
                            Paket Wisata
                        </a>
                        <a href="{{ route('admin.umkm-products.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.umkm-products.*') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            UMKM
                        </a>
                        <a href="{{ route('admin.budaya.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.budaya.*') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                            Budaya
                        </a>
                        <a href="{{ route('admin.village-profile.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.village-profile.*') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                            Profil Desa
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="px-3 text-[10px] font-bold text-white/40 uppercase tracking-wider mb-2">Sistem</h3>
                    <div class="space-y-1">
                        <a href="{{ route('admin.village-stats.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.village-stats.*') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
                            Statistik
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                            Pengaturan
                        </a>
                        <a href="{{ route('admin.fonnte.device') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.fonnte.*') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><path d="M22 2 11 13"/><path d="M22 2v7h-7M2 2l7 7"/><path d="M22 22 11 11"/><path d="M22 22h-7M2 22l7-7"/></svg>
                            Status Fonnte
                        </a>
                        @if(auth()->user()->role === 'superadmin')
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-[#a3e635] before:rounded-r-md' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Kelola Admin
                        </a>
                        @endif
                    </div>
                </div>

            </nav>

            <div class="p-4 bg-black/10 border-t border-white/5">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-[#a3e635] font-bold text-xs shrink-0">
                        {{ substr(auth()->user()->nama, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->nama }}</p>
                        <p class="text-[11px] text-white/50 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="p-1.5 text-white/50 hover:text-white hover:bg-white/10 rounded-md transition-colors" title="Logout">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        @endauth

        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <div class="flex-1 overflow-y-auto p-6 lg:p-8">
                <div class="max-w-7xl mx-auto">
                    @if(session('success'))
                        <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 px-4 py-3.5 rounded-lg mb-6 text-sm flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="bg-red-50 border border-red-100 text-red-800 px-4 py-3.5 rounded-lg mb-6 text-sm flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
</body>
</html>
