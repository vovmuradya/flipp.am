<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PhoneVerificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    public function __construct(
        private readonly PhoneVerificationService $phoneVerification
    ) {
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'min:6', 'max:20', 'unique:users,phone'],
            'verification_code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (! $this->phoneVerification->verify($request->phone, $request->verification_code)) {
            return back()
                ->withErrors(['verification_code' => __('Неверный или просроченный код подтверждения.')])
                ->withInput($request->except('password', 'password_confirmation', 'verification_code'));
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $this->phoneVerification->normalize($request->phone),
            'phone_verified_at' => now(),
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard.index', absolute: false));
    }
}
