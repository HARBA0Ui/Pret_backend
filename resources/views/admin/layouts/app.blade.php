<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-soft: #f4f7fb;
            --ink: #0f172a;
            --accent: #e11d48;
            --accent-soft: #ffe4ec;
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: radial-gradient(1200px 500px at 10% -10%, #ffe4ec 0%, transparent 40%),
                        radial-gradient(900px 450px at 90% -5%, #e2e8f0 0%, transparent 45%),
                        var(--bg-soft);
            color: var(--ink);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(8px);
        }
    </style>
</head>
<body>
    @php
        $currentUser = auth()->user();
        $currentName = $currentUser->name ?? 'Admin';
        $initial = strtoupper(substr($currentName, 0, 1));
    @endphp

    <div class="min-h-screen flex">
        <aside class="w-72 p-5 hidden lg:flex lg:flex-col">
            <div class="glass-card rounded-2xl border border-white/50 shadow-sm p-5 h-full flex flex-col">
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Backoffice</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-slate-900">Tunisair Admin</h1>
                    <p class="mt-2 text-sm text-slate-500">Pilotage des demandes, prets et inscriptions.</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-3 mb-6 flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold">{{ $initial }}</div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $currentName }}</p>
                        <p class="text-xs text-slate-500">Administrateur</p>
                    </div>
                </div>

                <nav class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
                    <a href="{{ route('admin.registrations.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('admin.registrations.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">Inscriptions</a>
                    <a href="{{ route('admin.employees.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('admin.employees.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">Employes</a>
                    <a href="{{ route('admin.demandes.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('admin.demandes.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">Demandes</a>
                    <a href="{{ route('admin.prets.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('admin.prets.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">Prets</a>
                    <a href="{{ route('admin.chat.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('admin.chat.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">Support Chat</a>
                </nav>

                <div class="mt-auto pt-6">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full rounded-xl px-4 py-3 text-sm font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 transition">
                            Deconnexion
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="flex-1 p-4 md:p-6 lg:p-8">
            <div class="glass-card rounded-2xl border border-white/60 shadow-sm px-5 py-4 mb-4 md:mb-6 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">{{ now()->format('d/m/Y') }}</p>
                    <p class="text-lg md:text-xl font-bold text-slate-900">@yield('title', 'Admin')</p>
                </div>
                <div class="lg:hidden">
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg bg-slate-900 text-white px-3 py-2 text-xs font-semibold">Accueil</a>
                </div>
            </div>

            <div class="lg:hidden mb-4 glass-card rounded-xl border border-white/60 shadow-sm p-3">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.registrations.index') }}" class="rounded-lg bg-rose-50 text-rose-700 px-3 py-2 text-xs font-semibold">Inscriptions</a>
                    <a href="{{ route('admin.employees.index') }}" class="rounded-lg bg-slate-100 text-slate-700 px-3 py-2 text-xs font-semibold">Employes</a>
                    <a href="{{ route('admin.demandes.index') }}" class="rounded-lg bg-slate-100 text-slate-700 px-3 py-2 text-xs font-semibold">Demandes</a>
                    <a href="{{ route('admin.prets.index') }}" class="rounded-lg bg-slate-100 text-slate-700 px-3 py-2 text-xs font-semibold">Prets</a>
                    <a href="{{ route('admin.chat.index') }}" class="rounded-lg bg-slate-100 text-slate-700 px-3 py-2 text-xs font-semibold">Support</a>
                </div>
            </div>

            <div class="space-y-6">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
