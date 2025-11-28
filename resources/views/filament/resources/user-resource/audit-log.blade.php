<div class="space-y-4">
    @forelse($logs as $log)
        <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <span class="font-semibold text-sm">{{ ucfirst($log->action) }}</span>
                    <span class="text-xs text-gray-500 ml-2">{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                </div>
                @if($log->user)
                    <span class="text-xs text-gray-500">By: {{ $log->user->name }}</span>
                @endif
            </div>
            
            @if($log->changes)
                <div class="mt-2 space-y-1">
                    @foreach($log->changes as $field => $change)
                        <div class="text-xs">
                            <span class="font-medium">{{ $field }}:</span>
                            @if(is_array($change))
                                @if(isset($change['before']) && isset($change['after']))
                                    <span class="text-red-600 line-through">{{ $change['before'] ?? 'null' }}</span>
                                    <span class="text-green-600">→ {{ $change['after'] ?? 'null' }}</span>
                                @else
                                    <span class="text-green-600">{{ json_encode($change) }}</span>
                                @endif
                            @else
                                <span class="text-green-600">{{ $change }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if($log->ip_address)
                <div class="mt-2 text-xs text-gray-400">
                    IP: {{ $log->ip_address }}
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-8 text-gray-500">
            No audit logs found for this user.
        </div>
    @endforelse
</div>

