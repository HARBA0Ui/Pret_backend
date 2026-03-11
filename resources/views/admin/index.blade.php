@extends('admin.layouts.app')

@section('title', 'Gestion des Demandes')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Demandes</h1>
    <p class="text-gray-600 mt-1">Gérer toutes les demandes</p>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select name="type" class="w-full border border-gray-300 rounded-lg px-4 py-2" onchange="this.form.submit()">
                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Tous les types</option>
                <option value="aide-sociale" {{ $type === 'aide-sociale' ? 'selected' : '' }}>Aide Sociale</option>
                <option value="don-scolaire" {{ $type === 'don-scolaire' ? 'selected' : '' }}>Don Scolaire</option>
                <option value="salaire-collective" {{ $type === 'salaire-collective' ? 'selected' : '' }}>Salaire Collective</option>
                <option value="pret-exceptionnel" {{ $type === 'pret-exceptionnel' ? 'selected' : '' }}>Prêt Exceptionnel</option>
                <option value="pret-hajj" {{ $type === 'pret-hajj' ? 'selected' : '' }}>Prêt Hajj</option>
                <option value="remboursement" {{ $type === 'remboursement' ? 'selected' : '' }}>Remboursement Anticipé</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
            <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2" onchange="this.form.submit()">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tous les statuts</option>
                <option value="En attente" {{ $status === 'En attente' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approuvé</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejeté</option>
            </select>
        </div>
    </form>
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
                    {{ number_format($demande->amountRequested, 2) }} TND
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $demande->submittedAt?->format('d/m/Y H:i') ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($demande->status === 'En attente')
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        En attente
                    </span>
                    @elseif($demande->status === 'approved')
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        Approuvé
                    </span>
                    @else
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                        Rejeté
                    </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <a href="{{ route('admin.demandes.show', ['id' => $demande->_id, 'type' => strtolower(str_replace(' ', '-', $demande->type))]) }}"
                        class="text-blue-600 hover:text-blue-800">
                        Voir détails
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    Aucune demande trouvée
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
