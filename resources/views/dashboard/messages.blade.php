<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Мои сообщения') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 divide-y">
                    @forelse($conversations as $message)
                        @php
                            $participant = $message->sender_id === auth()->id() ? $message->receiver : $message->sender;
                        @endphp
                        <a href="{{ route('dashboard.conversation.show', ['listing' => $message->listing, 'participant' => $participant]) }}" class="block p-4 hover:bg-gray-50">
                            <div class="font-semibold">{{ __('Переписка с :name', ['name' => $participant->name]) }}</div>
                            <div class="text-sm text-gray-600">{{ __('По объявлению:') }} <span class="text-indigo-600">{{ $message->listing->title }}</span></div>
                            <div class="text-sm text-gray-500 mt-1 truncate">
                                {{ $message->sender_id === auth()->id() ? __('Вы: ') : '' }}{{ $message->body }}
                            </div>
                        </a>
                    @empty
                        <p class="p-4">{{ __('У вас пока нет сообщений.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
