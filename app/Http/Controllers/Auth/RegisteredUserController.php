<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;





use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // Create the user
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    // 👉 Option A: first user becomes Super Admin, others get Admin (or whatever you like)
    if (User::count() === 1) {
        // ensure role exists (safe even if already there)
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user->assignRole('Super Admin');
    } else {
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $user->assignRole('Admin');
    }

    // (Optional) also give a permission that lets non–Super Admins into /admin
    Permission::firstOrCreate(['name' => 'access admin', 'guard_name' => 'web']);
    $user->givePermissionTo('access admin');

    event(new Registered($user));
    Auth::login($user);

    // 👉 Choose one redirect:
    // return redirect()->route('dashboard'); // Breeze dashboard
    return redirect('/admin');                // Filament panel
}
}
