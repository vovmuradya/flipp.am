<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Переписка с :name', ['name' => $participant->name]) }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">{{ __('По объявлению:') }} <a href="{{ route('listings.show', $listing) }}" class="text-indigo-600 font-bold">{{ $listing->title }}</a></div>

                    {{-- История чата --}}
                    <div class="space-y-4 mb-6">
                        @foreach($messages as $message)
                            <div class="p-3 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-blue-100 ml-auto w-4/5 text-right' : 'bg-gray-100 w-4/5' }}">
                                <p class="font-semibold text-sm text-left">{{ $message->sender->name }}:</p>
                                <p class="text-left">{{ $message->body }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $message->created_at->format('d.m.Y H:i') }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Форма для ответа --}}
                    <div class="border-t pt-6">
                        <form action="{{ route('dashboard.conversation.reply', ['listing' => $listing, 'participant' => $participant]) }}" method="POST">
                            @csrf
                            <textarea name="body" rows="4" class="w-full border-gray-300 rounded-md" placeholder="{{ __('Написать ответ...') }}" required></textarea>
                            <div class="mt-4"><x-primary-button>{{ __('Отправить') }}</x-primary-button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
