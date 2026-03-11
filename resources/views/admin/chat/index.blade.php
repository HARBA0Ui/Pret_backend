@extends('admin.layouts.app')

@section('title', 'Support Chat')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Support Chat</h1>
    <p class="text-gray-600 mt-1">Conversations employes et support</p>
</div>

<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Employe (nom, email ou matricule)</label>
            <input
                type="text"
                name="employee_search"
                value="{{ $employeeSearch ?? '' }}"
                placeholder="Ex: Ahmed / ahmed@... / AF003D"
                class="w-full border border-gray-300 rounded-lg px-4 py-2"
            />
        </div>

        <div class="flex gap-2">
            <button class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800 font-semibold">
                Filtrer
            </button>

            <a href="{{ route('admin.chat.index') }}"
               class="px-4 py-2 rounded-lg bg-gray-100 text-gray-900 hover:bg-gray-200 font-semibold">
                Reinitialiser
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employe</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dernier message</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Derniere activite</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($conversations as $conversation)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <div class="font-medium">{{ $conversation->employee->name ?? 'N/A' }}</div>
                    <div class="text-xs text-gray-500">{{ $conversation->employee->email ?? '' }}</div>
                </td>

                <td class="px-6 py-4 text-sm text-gray-700">
                    {{ \Illuminate\Support\Str::limit($conversation->lastMessageText ?? 'Aucun message', 80) }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $conversation->lastMessageAt?->format('d/m/Y H:i') ?? 'N/A' }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center gap-3">
                        <a
                            href="{{ route('admin.chat.show', ['id' => $conversation->_id]) }}"
                            class="text-blue-600 hover:text-blue-800 font-medium"
                        >
                            Ouvrir →
                        </a>
                        <form method="POST"
                              action="{{ route('admin.chat.destroy', ['id' => $conversation->_id]) }}"
                              onsubmit="return confirm('Supprimer cette conversation et tous ses messages ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                Supprimer
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <p class="font-medium">Aucune conversation trouvee</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
