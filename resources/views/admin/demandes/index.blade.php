@extends('admin.layouts.app')

@section('title', 'Gestion des Demandes')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Demandes</h1>
    <p class="text-gray-600 mt-1">Gérer toutes les demandes</p>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select name="type" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Tous les types</option>
                <option value="aide-sociale" {{ $type === 'aide-sociale' ? 'selected' : '' }}>Aide Sociale</option>
                <option value="don-scolaire" {{ $type === 'don-scolaire' ? 'selected' : '' }}>Don Scolaire</option>
                <option value="pret-exceptionnel" {{ $type === 'pret-exceptionnel' ? 'selected' : '' }}>Prêt Exceptionnel</option>
                <option value="pret-hajj" {{ $type === 'pret-hajj' ? 'selected' : '' }}>Prêt Hajj</option>
                <option value="remboursement" {{ $type === 'remboursement' ? 'selected' : '' }}>Remboursement Anticipé</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
            <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tous les statuts</option>
                <option value="En attente" {{ $status === 'En attente' ? 'selected' : '' }}>En attente</option>
                <option value="Approuvée" {{ $status === 'Approuvée' ? 'selected' : '' }}>Approuvée</option>
                <option value="Rejetée" {{ $status === 'Rejetée' ? 'selected' : '' }}>Rejetée</option>
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

            <a href="{{ route('admin.demandes.index') }}"
               class="px-4 py-2 rounded-lg bg-gray-100 text-gray-900 hover:bg-gray-200 font-semibold">
                Réinitialiser
            </a>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-sm text-gray-600">Total</p>
        <p class="text-2xl font-bold text-gray-900">{{ $demandes->count() }}</p>
    </div>

    <div class="bg-yellow-50 rounded-lg shadow p-4">
        <p class="text-sm text-yellow-700">En attente</p>
        <p class="text-2xl font-bold text-yellow-800">{{ $demandes->where('status', 'En attente')->count() }}</p>
    </div>

    <div class="bg-green-50 rounded-lg shadow p-4">
        <p class="text-sm text-green-700">Approuvées</p>
        <p class="text-2xl font-bold text-green-800">{{ $demandes->where('status', 'Approuvée')->count() }}</p>
    </div>
</div>

<!-- Demandes Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employé</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($demandes as $demande)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $demande->type }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $demande->employee->name ?? 'N/A' }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                    {{ number_format((float) $demande->amountRequested, 2) }} TND
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $demande->submittedAt?->format('d/m/Y H:i') ?? 'N/A' }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap">
                    @if($demande->status === 'En attente')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                    @elseif($demande->status === 'Approuvée')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approuvée</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejetée</span>
                    @endif
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <a
                        href="{{ route('admin.demandes.show', ['id' => $demande->_id, 'type' => $demande->typeSlug ?? request('type')]) }}"
                        class="text-blue-600 hover:text-blue-800 font-medium"
                    >
                        Voir détails →
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <span class="text-4xl mb-2">📭</span>
                        <p class="font-medium">Aucune demande trouvée</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
