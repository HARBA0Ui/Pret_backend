@extends('admin.layouts.app')

@section('title', 'Gestion des Prêts')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Prêts</h1>
    <p class="text-gray-600 mt-1">Gérer tous les prêts</p>
</div>

<!-- Filter -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
            <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tous les statuts</option>
                <option value="active" {{ ($status === 'active' || $status === 'Actif') ? 'selected' : '' }}>Actif</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Complété</option>
                <option value="defaulted" {{ $status === 'defaulted' ? 'selected' : '' }}>En défaut</option>
            </select>
        </div>

        <!-- ✅ NEW: Employee search input -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Employé (nom, email ou matricule)</label>
            <input
                type="text"
                name="employee_search"
                value="{{ $employeeSearch ?? '' }}"
                placeholder="Ex: Ahmed / ahmed@... / AF003D"
                class="w-full border border-gray-300 rounded-lg px-4 py-2"
            />
        </div>

        <div class="flex gap-2">
            <div class="w-1/2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Du</label>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2" />
            </div>
            <div class="w-1/2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Au</label>
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2" />
            </div>
        </div>

        <div class="md:col-span-4 flex gap-2">
            <button class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800 font-semibold">
                Filtrer
            </button>

            <a href="{{ route('admin.prets.index') }}"
               class="px-4 py-2 rounded-lg bg-gray-100 text-gray-900 hover:bg-gray-200 font-semibold">
                Réinitialiser
            </a>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-sm text-gray-600">Total Prêts</p>
        <p class="text-2xl font-bold text-gray-900">{{ $prets->count() }}</p>
    </div>
    <div class="bg-green-50 rounded-lg shadow p-4">
        <p class="text-sm text-green-700">Actifs</p>
        <p class="text-2xl font-bold text-green-800">{{ $prets->where('status', 'active')->count() }}</p>
    </div>
    <div class="bg-blue-50 rounded-lg shadow p-4">
        <p class="text-sm text-blue-700">Montant Total</p>
        <p class="text-2xl font-bold text-blue-800">{{ number_format($prets->sum('amount'), 2) }} TND</p>
    </div>
</div>

<!-- Prets Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employé</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taux</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solde Restant</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($prets as $pret)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $pret->employee->name ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                    {{ number_format($pret->amount, 2) }} TND
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $pret->interestRate ?? 0 }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">
                    {{ number_format($pret->remainingBalance ?? $pret->amount, 2) }} TND
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($pret->status === 'active' || $pret->status === 'Actif')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                    @elseif($pret->status === 'completed')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Complété</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">En défaut</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <a href="{{ route('admin.prets.show', $pret->_id) }}"
                       class="text-blue-600 hover:text-blue-800 font-medium">
                        Voir détails →
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <span class="text-4xl mb-2">💰</span>
                        <p class="font-medium">Aucun prêt trouvé</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
