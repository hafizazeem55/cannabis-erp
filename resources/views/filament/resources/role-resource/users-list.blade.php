<div class="space-y-2">
    @forelse($users as $user)
        <div class="border rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
            <div class="flex justify-between items-center">
                <div>
                    <span class="font-semibold">{{ $user->name }}</span>
                    <span class="text-sm text-gray-500 ml-2">{{ $user->email }}</span>
                </div>
                @if($user->organization)
                    <span class="text-xs badge badge-info">{{ $user->organization->name }}</span>
                @endif
            </div>
            @if($user->position)
                <div class="text-xs text-gray-500 mt-1">{{ $user->position }}</div>
            @endif
        </div>
    @empty
        <div class="text-center py-8 text-gray-500">
            No users assigned to this role.
        </div>
    @endforelse
</div>

