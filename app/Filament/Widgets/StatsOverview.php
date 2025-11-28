<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\User;
use App\Models\Organization;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->hasRole('Administrator') || $user?->can('view dashboard');
    }

    protected function getCards(): array
    {
        $user = auth()->user();
        $cards = [];

        // Users card - only if user can manage users
        if ($user?->can('manage users') || $user?->hasRole('Administrator')) {
            $cards[] = Card::make('Total Users', User::count())
                ->description('Active: ' . User::where('is_active', true)->count())
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5]);
        }

        // Roles card - only if user can manage roles
        if ($user?->can('manage roles') || $user?->hasRole('Administrator')) {
            $cards[] = Card::make('Total Roles', Role::count())
                ->description('Active roles in system')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info')
                ->chart([2, 3, 4, 3, 4, 5, 4]);
        }

        // Permissions card - only if user can manage permissions
        if ($user?->can('manage permissions') || $user?->hasRole('Administrator')) {
            $cards[] = Card::make('Total Permissions', Permission::count())
                ->description('Available permissions')
                ->descriptionIcon('heroicon-m-key')
                ->color('warning')
                ->chart([5, 6, 7, 8, 9, 10, 11]);
        }

        // Organizations card - only for administrators
        if ($user?->hasRole('Administrator')) {
            $cards[] = Card::make('Organizations', Organization::count())
                ->description('Active: ' . Organization::where('is_active', true)->count())
                ->descriptionIcon('heroicon-m-building-office')
                ->color('danger')
                ->chart([1, 2, 2, 3, 3, 4, 4]);
        }

        // User's own info card - always visible
        $cards[] = Card::make('Your Roles', $user?->roles->count() ?? 0)
            ->description('Roles assigned to you')
            ->descriptionIcon('heroicon-m-user-circle')
            ->color('gray');

        // If no cards, show a welcome message
        if (empty($cards)) {
            $cards[] = Card::make('Welcome', 'You don\'t have permission to view statistics')
                ->description('Contact your administrator for access')
                ->color('gray');
        }

        return $cards;
    }
}
