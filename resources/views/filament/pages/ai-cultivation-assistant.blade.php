<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Chat Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">AI Cultivation Assistant</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Ask questions about your cultivation data, batches, and best practices
                </p>
            </div>
            
            <x-filament::button 
                wire:click="clearChat"
                color="gray"
                size="sm"
            >
                Clear Chat
            </x-filament::button>
        </div>

        {{-- Chat History --}}
        <div class="border rounded-lg p-4 bg-white dark:bg-gray-800 min-h-[400px] max-h-[600px] overflow-y-auto space-y-4">
            @if (empty($chatHistory))
                <div class="text-center text-gray-500 dark:text-gray-400 py-20">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <p class="text-lg font-medium">No messages yet</p>
                    <p class="text-sm">Start a conversation by asking a question below</p>
                </div>
            @else
                @foreach ($chatHistory as $chat)
                    {{-- User Query --}}
                    <div class="flex justify-end">
                        <div class="bg-primary-500 text-white rounded-lg px-4 py-2 max-w-[80%]">
                            <p class="text-sm">{{ $chat['query'] }}</p>
                            <span class="text-xs opacity-75">{{ $chat['timestamp'] }}</span>
                        </div>
                    </div>

                    {{-- AI Response --}}
                    <div class="flex justify-start">
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 max-w-[80%]">
                            <div class="prose dark:prose-invert max-w-none">
                                <p class="text-sm whitespace-pre-line">{{ $chat['response'] }}</p>
                            </div>
                            <span class="text-xs text-gray-500">{{ $chat['timestamp'] }}</span>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Current Response --}}
            @if ($response)
                <div class="flex justify-start">
                    <div class="bg-green-100 dark:bg-green-900 rounded-lg px-4 py-2 max-w-[80%]">
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-sm whitespace-pre-line">{{ $response }}</p>
                        </div>
                        <span class="text-xs text-gray-500">Just now</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Input Form --}}
        <form wire:submit="sendQuery" class="space-y-4">
            {{ $this->form }}
            
            <div class="flex gap-2">
                <x-filament::button 
                    type="submit"
                    class="flex-1"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Send Question
                </x-filament::button>
            </div>
        </form>

        {{-- Tips --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">💡 Tips for better answers:</h3>
            <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                <li>• Select a specific batch for batch-related questions</li>
                <li>• Ask about environmental conditions, plant health, or harvest predictions</li>
                <li>• Reference batch codes (e.g., "What's the status of B-2025-0001?")</li>
                <li>• Ask for cultivation best practices and recommendations</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
