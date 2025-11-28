<?php

namespace App\Filament\Resources;

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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Permissions';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Permission Information')
                ->schema([
                    TextInput::make('name')
                        ->label('Permission Name')
                        ->unique(ignoreRecord: true)
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g., manage users, view dashboard')
                        ->helperText('Use lowercase with spaces. Example: "manage users", "view dashboard"')
                        ->formatStateUsing(fn ($state) => $state)
                        ->dehydrateStateUsing(fn ($state) => strtolower(trim($state))),

                    Select::make('guard_name')
                        ->label('Guard Name')
                        ->options([
                            'web' => 'Web',
                            'api' => 'API',
                        ])
                        ->default('web')
                        ->required()
                        ->disabled(fn ($record) => $record !== null)
                        ->helperText('Guard determines which authentication system to use'),

                    Textarea::make('description')
                        ->maxLength(500)
                        ->rows(3)
                        ->label('Description')
                        ->placeholder('Brief description of what this permission allows')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Roles')
                ->schema([
                    Select::make('roles')
                        ->label('Roles with this Permission')
                        ->multiple()
                        ->preload()
                        ->relationship('roles', 'name')
                        ->searchable()
                        ->helperText('Roles that have been assigned this permission')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->visible(fn ($record) => $record !== null),
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
                    ->label('Permission Name')
                    ->icon('heroicon-o-key')
                    ->copyable(),

                TextColumn::make('guard_name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'web' => 'success',
                        'api' => 'info',
                        default => 'gray',
                    })
                    ->label('Guard'),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('info')
                    ->wrap()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->roles->pluck('name')->join(', ')),

                TextColumn::make('roles_count')
                    ->label('Role Count')
                    ->counts('roles')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => (string) $state),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('guard_name')
                    ->label('Guard')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ]),

                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_roles')
                    ->label('View Roles')
                    ->icon('heroicon-o-shield-check')
                    ->color('info')
                    ->modalHeading(fn ($record) => "Roles with {$record->name} permission")
                    ->modalContent(fn ($record) => view('filament.resources.permission-resource.roles-list', [
                        'roles' => $record->roles,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('manage permissions') || auth()->user()?->hasRole('Administrator'))
                    ->disabled(fn ($record) => $record->roles()->count() > 0),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('manage permissions') || auth()->user()?->hasRole('Administrator'))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name')
            ->groups([
                Tables\Grouping\Group::make('guard_name')
                    ->label('Guard')
                    ->collapsible(),
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage permissions') || auth()->user()?->hasRole('Administrator');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage permissions') || auth()->user()?->hasRole('Administrator');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage permissions') || auth()->user()?->hasRole('Administrator');
    }

    public static function canDelete($record): bool
    {
        // Prevent deleting permissions assigned to roles
        if ($record->roles()->count() > 0) {
            return false;
        }
        return auth()->user()?->can('manage permissions') || auth()->user()?->hasRole('Administrator');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PermissionResource\Pages\ListPermissions::route('/'),
            'create' => \App\Filament\Resources\PermissionResource\Pages\CreatePermission::route('/create'),
            'edit' => \App\Filament\Resources\PermissionResource\Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
