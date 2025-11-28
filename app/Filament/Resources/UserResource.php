<?php

namespace App\Filament\Resources;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Users';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('User Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('Full Name')
                        ->placeholder('John Doe'),

                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->label('Email Address')
                        ->placeholder('john@example.com'),

                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(20)
                        ->label('Phone Number')
                        ->placeholder('+1 234 567 8900'),

                    TextInput::make('position')
                        ->maxLength(255)
                        ->label('Job Position')
                        ->placeholder('e.g., QA Manager, Cultivation Operator'),
                ])
                ->columns(2),

            Section::make('Organization & Access')
                ->schema([
                    Select::make('organization_id')
                        ->label('Organization')
                        ->relationship('organization', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')->required(),
                            TextInput::make('code')->required()->unique('organizations', 'code'),
                            TextInput::make('timezone')->default('UTC'),
                            TextInput::make('country'),
                        ])
                        ->visible(fn () => auth()->user()?->hasRole('Administrator')),

                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Inactive users cannot access the system'),
                ])
                ->columns(2),

            Section::make('Security')
                ->schema([
                    TextInput::make('password')
                        ->password()
                        ->label('Password')
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context) => $context === 'create')
                        ->minLength(8)
                        ->helperText('Minimum 8 characters')
                        ->revealable(),

                    TextInput::make('password_confirmation')
                        ->password()
                        ->label('Confirm Password')
                        ->same('password')
                        ->dehydrated(false)
                        ->required(fn (Forms\Get $get) => filled($get('password')))
                        ->revealable(),
                ])
                ->columns(2)
                ->visible(fn (string $context) => $context === 'create' || auth()->user()?->can('manage users')),

            Section::make('Roles & Permissions')
                ->schema([
                    Select::make('roles')
                        ->label('Roles')
                        ->multiple()
                        ->preload()
                        ->relationship('roles', 'name')
                        ->searchable()
                        ->helperText('Select one or more roles for this user'),

                    Select::make('permissions')
                        ->label('Direct Permissions')
                        ->multiple()
                        ->options(fn () => Permission::orderBy('name')->pluck('name', 'name'))
                        ->searchable()
                        ->dehydrated(true)
                        ->helperText('Additional permissions beyond roles')
                        ->saveRelationshipsUsing(function ($component, $state, $record) {
                            $record->syncPermissions($state ?? []);
                        })
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if ($record) {
                                $component->state($record->permissions->pluck('name')->all());
                            }
                        }),
                ])
                ->columns(1),
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
                    ->label('Name'),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                TextColumn::make('organization.name')
                    ->label('Organization')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->visible(fn () => auth()->user()?->hasRole('Administrator')),

                TextColumn::make('position')
                    ->label('Position')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Administrator' => 'danger',
                        'QA Manager' => 'warning',
                        default => 'success',
                    })
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organization_id')
                    ->label('Organization')
                    ->relationship('organization', 'name')
                    ->visible(fn () => auth()->user()?->hasRole('Administrator')),

                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple(),

                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All users')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Created from'),
                        DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_audit')
                    ->label('Audit Log')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->modalHeading('User Audit Log')
                    ->modalContent(fn (User $record) => view('filament.resources.user-resource.audit-log', [
                        'logs' => AuditLog::where('user_id', $record->id)
                            ->orWhere(function ($query) use ($record) {
                                $query->where('model_type', User::class)
                                    ->where('model_id', $record->id);
                            })
                            ->latest()
                            ->limit(50)
                            ->get(),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->visible(fn () => auth()->user()?->can('manage users') || auth()->user()?->hasRole('Administrator')),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('manage users') || auth()->user()?->hasRole('Administrator')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('manage users') || auth()->user()?->hasRole('Administrator')),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('manage users') || auth()->user()?->hasRole('Administrator')),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('manage users') || auth()->user()?->hasRole('Administrator')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage users') || auth()->user()?->hasRole('Administrator');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage users') || auth()->user()?->hasRole('Administrator');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage users') || auth()->user()?->hasRole('Administrator');
    }

    public static function canDelete($record): bool
    {
        // Prevent deleting yourself
        if ($record->id === auth()->id()) {
            return false;
        }
        return auth()->user()?->can('manage users') || auth()->user()?->hasRole('Administrator');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\UserResource\Pages\ListUsers::route('/'),
            'create' => \App\Filament\Resources\UserResource\Pages\CreateUser::route('/create'),
            'edit' => \App\Filament\Resources\UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('is_active', true)->count();
    }
}
