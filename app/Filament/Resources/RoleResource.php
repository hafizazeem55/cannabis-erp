<?php

namespace App\Filament\Resources;

use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Roles';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return $parts[0] ?? 'Other';
        });

        return $form->schema([
            Section::make('Role Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->label('Role Name')
                        ->placeholder('e.g., QA Manager, Cultivation Operator')
                        ->helperText('A unique name for this role'),

                    Textarea::make('description')
                        ->maxLength(500)
                        ->rows(3)
                        ->label('Description')
                        ->placeholder('Brief description of this role\'s responsibilities')
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make('Permissions')
                ->schema([
                    Select::make('permissions')
                        ->label('Assign Permissions')
                        ->multiple()
                        ->preload()
                        ->relationship('permissions', 'name')
                        ->searchable()
                        ->options(function () use ($permissions) {
                            return $permissions->pluck('name', 'id');
                        })
                        ->helperText('Select permissions for this role. Users with this role will inherit all selected permissions.')
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->description('Select the permissions that users with this role will have access to.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label('Role Name')
                    ->icon('heroicon-o-shield-check'),

                TextColumn::make('permissions.name')
                    ->label('Permissions')
                    ->badge()
                    ->color('success')
                    ->wrap()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->permissions->pluck('name')->join(', ')),

                TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => (string) $state),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('permissions')
                    ->label('Permission')
                    ->relationship('permissions', 'name')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_users')
                    ->label('View Users')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->modalHeading(fn ($record) => "Users with {$record->name} role")
                    ->modalContent(fn ($record) => view('filament.resources.role-resource.users-list', [
                        'users' => $record->users()->with('organization')->get(),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => 
                        $record->name !== 'Administrator' && 
                        (auth()->user()?->can('manage roles') || auth()->user()?->hasRole('Administrator'))
                    )
                    ->disabled(fn ($record) => $record->users()->count() > 0),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('manage roles') || auth()->user()?->hasRole('Administrator'))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage roles') || auth()->user()?->hasRole('Administrator');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage roles') || auth()->user()?->hasRole('Administrator');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage roles') || auth()->user()?->hasRole('Administrator');
    }

    public static function canDelete($record): bool
    {
        // Prevent deleting Administrator role
        if ($record->name === 'Administrator') {
            return false;
        }
        // Prevent deleting roles with users
        if ($record->users()->count() > 0) {
            return false;
        }
        return auth()->user()?->can('manage roles') || auth()->user()?->hasRole('Administrator');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\RoleResource\Pages\ListRoles::route('/'),
            'create' => \App\Filament\Resources\RoleResource\Pages\CreateRole::route('/create'),
            'edit' => \App\Filament\Resources\RoleResource\Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
