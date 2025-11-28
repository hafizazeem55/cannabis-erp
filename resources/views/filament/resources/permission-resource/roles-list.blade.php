<div class="space-y-2">
    @forelse($roles as $role)
        <div class="border rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
            <div class="flex justify-between items-center">
                <span class="font-semibold">{{ $role->name }}</span>
                <span class="text-xs badge badge-info">{{ $role->users()->count() }} users</span>
            </div>
        </div>
    @empty
        <div class="text-center py-8 text-gray-500">
            No roles have this permission.
        </div>
    @endforelse
</div>

