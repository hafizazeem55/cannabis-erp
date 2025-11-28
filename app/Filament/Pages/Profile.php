<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.pages.profile';
    protected static ?string $navigationLabel = 'My Profile';
    protected static ?int $navigationSort = 100;
    protected static ?string $navigationGroup = null;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(auth()->user()->toArray());
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Full Name'),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Email Address'),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20)
                            ->label('Phone Number'),

                        TextInput::make('position')
                            ->maxLength(255)
                            ->label('Job Position'),
                    ])
                    ->columns(2),

                Section::make('Change Password')
                    ->schema([
                        TextInput::make('current_password')
                            ->password()
                            ->label('Current Password')
                            ->required(fn ($get) => filled($get('new_password')))
                            ->revealable(),

                        TextInput::make('new_password')
                            ->password()
                            ->label('New Password')
                            ->minLength(8)
                            ->same('new_password_confirmation')
                            ->required(fn ($get) => filled($get('current_password')))
                            ->revealable(),

                        TextInput::make('new_password_confirmation')
                            ->password()
                            ->label('Confirm New Password')
                            ->required(fn ($get) => filled($get('new_password')))
                            ->revealable(),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Account Information')
                    ->schema([
                        TextInput::make('organization.name')
                            ->label('Organization')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('roles_display')
                            ->label('Your Roles')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn () => auth()->user()->roles->pluck('name')->join(', ')),

                        TextInput::make('permissions_display')
                            ->label('Your Permissions')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn () => auth()->user()->getAllPermissions()->pluck('name')->join(', '))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn () => auth()->user()->organization || auth()->user()->roles->count() > 0),
            ])
            ->statePath('data')
            ->model(auth()->user());
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        // Handle password change
        if (isset($data['current_password']) && filled($data['current_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                $this->addError('data.current_password', 'Current password is incorrect.');
                return;
            }

            if (isset($data['new_password']) && filled($data['new_password'])) {
                $data['password'] = Hash::make($data['new_password']);
            }
        }

        // Remove password fields from update
        unset($data['current_password'], $data['new_password'], $data['new_password_confirmation']);

        $user->update($data);

        // Log the update
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model_type' => User::class,
            'model_id' => $user->id,
            'changes' => ['profile_updated' => true],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->notify('success', 'Profile updated successfully!');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canAccess(): bool
    {
        return true; // All authenticated users can access their profile
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
        ];
    }
}

