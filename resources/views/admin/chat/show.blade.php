@extends('admin.layouts.app')

@section('title', 'Support Chat')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Conversation Support</h1>
    <p class="text-gray-600 mt-1">
        Employe: {{ $conversation->employee->name ?? 'N/A' }} ({{ $conversation->employee->email ?? 'N/A' }})
    </p>
    <div class="mt-2 flex items-center gap-3">
        <a href="{{ route('admin.chat.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
            Retour a la liste
        </a>
        <form method="POST"
              action="{{ route('admin.chat.destroy', ['id' => $conversation->_id]) }}"
              onsubmit="return confirm('Supprimer cette conversation et tous ses messages ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-semibold">
                Supprimer la conversation
            </button>
        </form>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 flex flex-col h-[70vh]">
    <div class="flex-1 overflow-y-auto p-4 space-y-3">
        @forelse($messages as $message)
            @php
                $isAdmin = ($message->senderRole ?? '') === 'admin';
                $senderName = $isAdmin ? 'Admin' : ($conversation->employee->name ?? 'Employe');
            @endphp

            <div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%] rounded-2xl px-4 py-2 text-sm {{ $isAdmin ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-800' }}">
                    <div class="text-[11px] {{ $isAdmin ? 'text-gray-200' : 'text-gray-500' }}">
                        {{ $senderName }}
                    </div>
                    <div class="whitespace-pre-wrap">{{ $message->content }}</div>
                    <div class="text-[10px] {{ $isAdmin ? 'text-gray-300' : 'text-gray-400' }} mt-1 text-right">
                        {{ $message->sentAt?->format('d/m/Y H:i') ?? '' }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500">Aucun message pour le moment.</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.chat.messages.send', ['id' => $conversation->_id]) }}"
          class="border-t border-gray-200 p-4 flex gap-2 items-center">
        @csrf
        <input
            type="text"
            name="content"
            placeholder="Ecrire une reponse..."
            class="flex-1 border border-gray-300 rounded-lg px-3 py-2"
            required
        />
        <button
            type="submit"
            class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800"
        >
            Envoyer
        </button>
    </form>
</div>
@endsection
