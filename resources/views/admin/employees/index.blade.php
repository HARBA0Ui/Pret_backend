@extends('admin.layouts.app')

@section('title', 'Gestion Employes')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900">Employes</h1>
    <p class="text-slate-600 mt-1">Modifier la direction et supprimer un employe avec confirmation.</p>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        {{ $errors->first() }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-slate-500">Total employes</p>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
        <p class="text-sm text-emerald-700">Valides</p>
        <p class="text-2xl font-bold text-emerald-800">{{ $stats['accepted'] }}</p>
    </div>
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
        <p class="text-sm text-amber-700">En attente</p>
        <p class="text-2xl font-bold text-amber-800">{{ $stats['pending'] }}</p>
    </div>
</div>

<div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Statut</label>
            <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tous</option>
                <option value="accepted" {{ $status === 'accepted' ? 'selected' : '' }}>Valides</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>En attente</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Direction</label>
            <select name="direction" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="all" {{ $direction === 'all' ? 'selected' : '' }}>Toutes</option>
                @foreach($directionOptions as $directionValue => $directionLabel)
                    <option value="{{ $directionValue }}" {{ $direction === $directionValue ? 'selected' : '' }}>
                        {{ $directionLabel }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Recherche</label>
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Nom, email ou matricule"
                class="w-full rounded-lg border border-slate-300 px-3 py-2"
            />
        </div>

        <div class="flex gap-2">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-white font-semibold hover:bg-slate-800">Filtrer</button>
            <a href="{{ route('admin.employees.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-slate-900 font-semibold hover:bg-slate-200">Reinitialiser</a>
        </div>
    </form>
</div>

<div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Employe</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Coordonnees</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Direction</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($employees as $employee)
                @php
                    $attributes = $employee->getAttributes();
                    $accepted = array_key_exists('isEmployeeAccepted', $attributes)
                        ? (bool) ($employee->isEmployeeAccepted ?? false)
                        : true;
                    $photoUrl = !empty($employee->profilePicturePath)
                        ? url(\Illuminate\Support\Facades\Storage::url($employee->profilePicturePath))
                        : null;
                @endphp

                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="Photo profil" class="h-10 w-10 rounded-full object-cover border border-slate-200" />
                            @else
                                <div class="h-10 w-10 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($employee->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-slate-900">{{ $employee->name }}</p>
                                <p class="text-xs text-slate-500">Matricule: {{ $employee->matricule ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4 text-sm text-slate-700">
                        <p>{{ $employee->email }}</p>
                        <p class="text-xs text-slate-500">{{ $employee->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                    </td>

                    <td class="px-6 py-4">
                        <form method="POST" action="{{ route('admin.employees.direction.update', $employee->_id) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="direction" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach($directionOptions as $directionValue => $directionLabel)
                                    <option value="{{ $directionValue }}" {{ ($employee->direction ?? '') === $directionValue ? 'selected' : '' }}>
                                        {{ $directionLabel }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="rounded-lg bg-slate-900 px-3 py-2 text-xs text-white font-semibold hover:bg-slate-800">
                                Enregistrer
                            </button>
                        </form>
                    </td>

                    <td class="px-6 py-4">
                        @if($accepted)
                            <span class="rounded-full bg-emerald-100 text-emerald-800 px-3 py-1 text-xs font-semibold">Valide</span>
                        @else
                            <span class="rounded-full bg-amber-100 text-amber-800 px-3 py-1 text-xs font-semibold">En attente</span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        <form method="POST"
                              action="{{ route('admin.employees.destroy', $employee->_id) }}"
                              onsubmit="return confirm('Supprimer cet employe et toutes ses donnees ? Cette action est irreversible.');">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-lg bg-rose-600 px-3 py-2 text-xs text-white font-semibold hover:bg-rose-700">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">Aucun employe trouve.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

