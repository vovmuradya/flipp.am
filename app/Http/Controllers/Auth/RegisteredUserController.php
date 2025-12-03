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
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
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
    public function store(Request $request)
    {
        $expectsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax();

        $normalizedPhone = $this->phoneVerification->normalizeArmenian((string) $request->input('phone', ''));

        if (!$normalizedPhone) {
            $error = ['phone' => [__('Введите армянский номер телефона в формате +374 XX XXX XXX.')]];

            if ($expectsJson) {
                return response()->json(['errors' => $error], 422);
            }

            return back()
                ->withErrors($error)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $request->merge([
            'phone' => $normalizedPhone,
        ]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'regex:/^\+374\d{8}$/', Rule::unique('users', 'phone')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            if ($expectsJson) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $normalizedPhone,
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        if ($expectsJson) {
            return response()->json([
                'redirect' => route('dashboard.index', absolute: false),
                'message' => __('Мы отправили ссылку для подтверждения email на указанную почту.'),
            ], 201);
        }

        return redirect(route('dashboard.index', absolute: false))
            ->with('status', __('Мы отправили ссылку для подтверждения email на указанную почту.'));
    }
}
