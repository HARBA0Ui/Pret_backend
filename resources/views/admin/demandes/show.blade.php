@extends('admin.layouts.app')

@section('title', 'Détails Demande')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.demandes.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
        ← Retour aux demandes
    </a>
</div>

<div class="bg-white rounded-lg shadow-lg p-6">
    <!-- Flash messages -->
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-red-800">
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header -->
    <div class="border-b pb-4 mb-6">
        <div class="flex justify-between items-start gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $demande->type }}</h1>
                <p class="text-gray-600 mt-1">Référence: #{{ substr((string) $demande->_id, -8) }}</p>
            </div>
            <div class="shrink-0">
                @if($demande->status === 'En attente')
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        En attente
                    </span>
                @elseif($demande->status === 'Approuvée')
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        Approuvée
                    </span>
                @else
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        Rejetée
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions: Approve / Reject -->
    @if($demande->status === 'En attente')
    <div class="flex flex-col lg:flex-row gap-3 mb-6">
        <!-- Approve -->
        <form
            method="POST"
            action="{{ route('admin.demandes.approuver', ['id' => $demande->_id, 'type' => $demande->typeSlug ?? request('type')]) }}"
            class="flex flex-col md:flex-row gap-2 items-start md:items-end bg-emerald-50 border border-emerald-200 p-4 rounded-lg w-full"
        >
            @csrf

            <div class="w-full md:w-auto">
                <label class="block text-sm font-medium text-emerald-800 mb-1">Montant approuvé (optionnel)</label>
                <input
                    name="approvedAmount"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="Par défaut: montant demandé"
                    class="w-full md:w-64 border border-emerald-200 bg-white px-3 py-2 rounded-lg"
                />
                <p class="text-xs text-emerald-700 mt-1">
                    Si vide, le montant approuvé sera égal au montant demandé.
                </p>
            </div>

            <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 font-semibold">
                Approuver
            </button>
        </form>

        <!-- Reject -->
        <form
            method="POST"
            action="{{ route('admin.demandes.rejeter', ['id' => $demande->_id, 'type' => $demande->typeSlug ?? request('type')]) }}"
            class="flex flex-col md:flex-row gap-2 items-start md:items-end bg-red-50 border border-red-200 p-4 rounded-lg w-full"
        >
            @csrf

            <div class="w-full">
                <label class="block text-sm font-medium text-red-800 mb-1">Motif de rejet</label>
                <input
                    name="reason"
                    type="text"
                    required
                    maxlength="255"
                    placeholder="Ex: documents manquants"
                    class="w-full border border-red-200 bg-white px-3 py-2 rounded-lg"
                />
            </div>

            <button class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 font-semibold">
                Rejeter
            </button>
        </form>
    </div>
    @endif

    <!-- Info Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Employee Info -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Employé</h3>
            <p class="text-lg font-semibold text-gray-900">{{ $demande->employee->name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600">{{ $demande->employee->email ?? 'N/A' }}</p>
        </div>

        <!-- Amount -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Montant demandé</h3>
            <p class="text-3xl font-bold text-red-600">{{ number_format((float) $demande->amountRequested, 2) }} TND</p>
        </div>

        <!-- Dates -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Date de soumission</h3>
            <p class="text-lg font-semibold text-gray-900">
                {{ $demande->submittedAt?->format('d/m/Y à H:i') ?? 'N/A' }}
            </p>
        </div>

        <!-- Approved Amount -->
        @if(!is_null($demande->approvedAmount))
        <div class="bg-green-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-green-700 mb-2">Montant approuvé</h3>
            <p class="text-3xl font-bold text-green-700">{{ number_format((float) $demande->approvedAmount, 2) }} TND</p>
        </div>
        @endif

        <!-- Reviewer -->
        @if($demande->reviewer)
        <div class="bg-gray-50 rounded-lg p-4 md:col-span-2">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Traité par</h3>
            <p class="text-gray-900 font-semibold">
                {{ $demande->reviewer->name }} ({{ $demande->reviewer->email }})
            </p>
            <p class="text-sm text-gray-600">
                Décision le : {{ $demande->decisionAt?->format('d/m/Y à H:i') ?? 'N/A' }}
            </p>
        </div>
        @endif
    </div>

    <!-- Reason & Description -->
    @if($demande->reason)
    <div class="mb-6">
        <h3 class="text-sm font-medium text-gray-700 mb-2">
            @if($demande->status === 'Rejetée')
                Motif de rejet
            @else
                Raison
            @endif
        </h3>
        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $demande->reason }}</p>
    </div>
    @endif

    @if($demande->description)
    <div class="mb-6">
        <h3 class="text-sm font-medium text-gray-700 mb-2">Description détaillée</h3>
        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg whitespace-pre-wrap">{{ $demande->description }}</p>
    </div>
    @endif

    <!-- Type-specific fields -->
    <div class="border-t pt-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Détails spécifiques</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if(in_array($demande->type, ['Aide Sociale', 'Prêt Exceptionnel', 'Prêt Hajj']))
                @if($demande->familySize)
                <div>
                    <span class="text-sm text-gray-600">Taille famille :</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ $demande->familySize }} personnes</span>
                </div>
                @endif

                @if($demande->monthlyIncome)
                <div>
                    <span class="text-sm text-gray-600">Revenu mensuel :</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ number_format((float) $demande->monthlyIncome, 2) }} TND</span>
                </div>
                @endif

            @elseif($demande->type === 'Don Scolaire')
                <div>
                    <span class="text-sm text-gray-600">Étudiant :</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ $demande->studentName }}</span>
                </div>

                <div>
                    <span class="text-sm text-gray-600">École :</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ $demande->schoolName }}</span>
                </div>

                <div>
                    <span class="text-sm text-gray-600">Niveau :</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ $demande->schoolLevel }}</span>
                </div>

                <div>
                    <span class="text-sm text-gray-600">Année académique :</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ $demande->academicYear }}</span>
                </div>

                @if($demande->tuitionAmount)
                <div>
                    <span class="text-sm text-gray-600">Frais de scolarité :</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ number_format((float) $demande->tuitionAmount, 2) }} TND</span>
                </div>
                @endif

                @if($demande->purpose)
                <div>
                    <span class="text-sm text-gray-600">Objet :</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ $demande->purpose }}</span>
                </div>
                @endif

            @elseif($demande->type === 'Remboursement Anticipé')
                @if($demande->loanIdToRepay)
                <div>
                    <span class="text-sm text-gray-600">ID du prêt :</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ $demande->loanIdToRepay }}</span>
                </div>
                @endif

                @if($demande->earlyRepaymentAmount)
                <div>
                    <span class="text-sm text-gray-600">Montant remboursement anticipé :</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ number_format((float) $demande->earlyRepaymentAmount, 2) }} TND</span>
                </div>
                @endif

                <div>
                    <span class="text-sm text-gray-600">Pénalité annulée :</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ $demande->penaltyWaived ? 'Oui' : 'Non' }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Attachments -->
    @if($demande->attachments && count($demande->attachments) > 0)
    <div class="border-t pt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pièces jointes</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($demande->attachments as $attachment)
            <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">📎</span>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $attachment->filename }}</p>
                        <p class="text-xs text-gray-500">{{ number_format($attachment->filesize / 1024, 2) }} KB</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('attachments.view', $attachment->_id) }}"
                       target="_blank"
                       class="px-3 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-sm font-medium">
                        Ouvrir
                    </a>

                    <a href="{{ route('attachments.download', $attachment->_id) }}"
                       class="px-3 py-1 bg-green-100 text-green-700 hover:bg-green-200 rounded text-sm font-medium">
                        Télécharger
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
