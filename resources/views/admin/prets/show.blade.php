@extends('admin.layouts.app')

@section('title', 'Détails Prêt')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.prets.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
        ← Retour aux prêts
    </a>
</div>

<div class="bg-white rounded-lg shadow-lg p-6">
    <!-- Header -->
    <div class="border-b pb-4 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Détails du Prêt</h1>
                <p class="text-gray-600 mt-1">Référence: #{{ substr($pret->_id, -8) }}</p>
            </div>
            <div>
                @if($pret->status === 'active')
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        Actif
                    </span>
                @elseif($pret->status === 'completed')
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        Complété
                    </span>
                @else
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        En défaut
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Employee Info -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Employé</h3>
            <p class="text-lg font-semibold text-gray-900">{{ $pret->employee->name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600">{{ $pret->employee->email ?? 'N/A' }}</p>
        </div>

        <!-- Amount -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Montant Initial</h3>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($pret->amount, 2) }} TND</p>
        </div>

        <!-- Interest Rate -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Taux d'Intérêt</h3>
            <p class="text-2xl font-bold text-blue-600">{{ $pret->interestRate ?? 0 }}%</p>
        </div>

        <!-- Remaining Balance -->
        <div class="bg-red-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-red-700 mb-2">Solde Restant</h3>
            <p class="text-3xl font-bold text-red-600">{{ number_format($pret->remainingBalance ?? $pret->amount, 2) }} TND</p>
        </div>
    </div>

    <!-- Mensualités -->
    @if($pret->mensualites && count($pret->mensualites) > 0)
    <div class="border-t pt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Mensualités ({{ count($pret->mensualites) }})</h3>
        <div class="bg-white rounded-lg overflow-hidden border">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date d'Échéance</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payé</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date de Paiement</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($pret->mensualites as $mensualite)
                    <tr class="{{ $mensualite->status === 'paid' ? 'bg-green-50' : '' }}">
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $mensualite->dueDate ? \Carbon\Carbon::parse($mensualite->dueDate)->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                            {{ number_format($mensualite->amount, 2) }} TND
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-green-600">
                            {{ number_format($mensualite->paidAmount ?? 0, 2) }} TND
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $mensualite->paidDate ? \Carbon\Carbon::parse($mensualite->paidDate)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($mensualite->status === 'paid')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Payé
                                </span>
                            @elseif($mensualite->status === 'overdue')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    En retard
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    En attente
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
