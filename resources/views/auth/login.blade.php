<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Manrope', sans-serif;
            background: radial-gradient(900px 450px at 10% -10%, #ffe4ec 0%, transparent 40%),
                        radial-gradient(900px 450px at 95% -20%, #dbeafe 0%, transparent 45%),
                        #f8fafc;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="grid w-full max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl md:grid-cols-2">
            <div class="hidden bg-gradient-to-br from-slate-900 to-slate-700 p-8 text-white md:flex md:flex-col">
                <h1 class="text-3xl font-extrabold">Backoffice Admin</h1>
                <p class="mt-3 text-sm text-slate-200">Console de gestion des inscriptions, demandes, prets et support.</p>
                <img src="/tunisiair.png" alt="Tunisair" class="mt-auto w-40 object-contain" />
            </div>

            <div class="p-8 md:p-10">
                <h2 class="text-2xl font-bold text-slate-900">Connexion</h2>
                <p class="mt-1 text-sm text-slate-600">Acces reserve aux administrateurs.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-rose-400 focus:ring focus:ring-rose-100"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Mot de passe</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-rose-400 focus:ring focus:ring-rose-100"
                        >
                    </div>

                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        <span class="text-sm text-slate-600">Se souvenir de moi</span>
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
                    >
                        Se connecter
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
