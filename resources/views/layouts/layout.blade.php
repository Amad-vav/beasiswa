<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SPK Matchmaking Beasiswa')</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS & JS (via Laravel Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js (via CDN for instant interactivity) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
        }
        .font-title {
            font-family: 'Outfit', sans-serif;
        }
        /* Custom background gradient glows */
        .glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(130px);
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen flex flex-col overflow-x-hidden selection:bg-emerald-500 selection:text-zinc-950">

    <!-- Glowing Background Orbs for SaaS depth -->
    <div class="glow-orb w-[500px] h-[500px] bg-emerald-500 top-[-100px] left-[-100px]"></div>
    <div class="glow-orb w-[600px] h-[600px] bg-blue-600 bottom-[-200px] right-[-100px] opacity-10"></div>
    <div class="glow-orb w-[400px] h-[400px] bg-emerald-600 top-[30%] right-[10%] opacity-5"></div>

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-40 w-full border-b border-zinc-800/80 bg-zinc-950/70 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-blue-600 flex items-center justify-center shadow-lg shadow-emerald-500/10 group-hover:scale-105 transition-transform duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <div>
                    <span class="font-title font-extrabold text-xl tracking-tight bg-gradient-to-r from-emerald-400 via-emerald-300 to-blue-400 bg-clip-text text-transparent">
                        ScholarMatch
                    </span>
                    <span class="block text-[10px] text-zinc-500 font-medium tracking-widest uppercase">SPK Decision Engine</span>
                </div>
            </a>
            
            <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-zinc-400">
                @auth
                    @if(!auth()->user()->is_admin)
                        <a href="{{ route('dashboard') }}" class="text-zinc-100 hover:text-emerald-400 transition-colors">Dashboard</a>
                        <a href="{{ route('scholarships.catalog') }}" class="hover:text-emerald-400 text-zinc-300 transition-colors">Katalog Beasiswa</a>
                        <a href="{{ route('premium.index') }}" class="hover:text-emerald-400 text-zinc-300 transition-colors flex items-center space-x-1.5">
                            <span>Premium Plan</span>
                            <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[9px] border border-emerald-500/20 font-bold uppercase tracking-wider">B2B</span>
                        </a>
                    @endif
                @else
                    <a href="{{ route('dashboard') }}" class="text-zinc-100 hover:text-emerald-400 transition-colors">Dashboard</a>
                @endauth
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.scholarships.index') }}" class="hover:text-emerald-400 text-zinc-300 transition-colors flex items-center space-x-1">
                            <span>Admin Dashboard</span>
                        </a>
                    @endif
                @endauth
            </nav>

            <div class="flex items-center space-x-4">
                <span class="text-xs text-zinc-500 hidden sm:inline-block">Versi 1.1 (SAW-Revised)</span>
                @auth
                    <div class="flex items-center space-x-3 bg-zinc-900/80 px-3 py-1.5 rounded-xl border border-zinc-800">
                        <div class="flex flex-col text-right">
                            <span class="text-xs font-semibold text-zinc-200">{{ auth()->user()->nama_lengkap }}</span>
                            <span class="text-[9px] font-bold tracking-wider uppercase {{ auth()->user()->is_premium ? 'text-amber-400' : 'text-zinc-450' }}">
                                {{ auth()->user()->is_premium ? 'Premium' : 'Reguler' }}
                            </span>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-zinc-800 border border-zinc-700 flex items-center justify-center font-bold text-sm text-emerald-400 font-title">
                            {{ strtoupper(substr(auth()->user()->nama_lengkap, 0, 1)) }}
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 text-zinc-400 hover:text-red-400 transition-colors flex items-center" title="Logout">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-xs font-semibold text-white rounded-xl group bg-gradient-to-br from-emerald-500 to-blue-600 group-hover:from-emerald-500 group-hover:to-blue-600 hover:text-white focus:ring-4 focus:outline-none focus:ring-emerald-800 transition-all duration-300 mt-2">
                        <span class="relative px-4 py-2 transition-all ease-in duration-75 bg-zinc-950 rounded-[10px] group-hover:bg-opacity-0">
                            Login
                        </span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow z-10 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Alerts section -->
        @if(session('success'))
            <div class="p-4 mb-6 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold flex items-center space-x-2">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-6 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-semibold flex items-center space-x-2">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-zinc-800/80 bg-zinc-950 py-6 mt-12 text-center text-sm text-zinc-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
            <div>
                <p>&copy; {{ date('Y') }} ScholarMatch System. Made for Beasiswa Isti & Putri.</p>
            </div>
            <div class="flex space-x-6">
                <a href="#" class="hover:text-zinc-300 transition-colors">Dokumen SAW</a>
                <a href="#" class="hover:text-zinc-300 transition-colors">Panduan Aplikasi</a>
                <a href="#" class="hover:text-zinc-300 transition-colors">API Spec</a>
            </div>
        </div>
    </footer>

</body>
</html>
