<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\PhoneVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly PhoneVerificationService $phoneVerification
    ) {
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function verifyPhone(Request $request): RedirectResponse
    {
        $input = $request->all();
        $input['phone'] = $this->phoneVerification->normalize((string) ($input['phone'] ?? ''));

        $validator = Validator::make(
            $input,
            [
                'phone' => [
                    'required',
                    'string',
                    'min:6',
                    'max:20',
                    Rule::unique('users', 'phone')->ignore($request->user()->id),
                ],
                'verification_code' => ['required', 'digits:6'],
            ],
            [],
            [
                'phone' => __('Телефон'),
                'verification_code' => __('Код подтверждения'),
            ]
        );

        if ($validator->fails()) {
            return back()->withErrors($validator, 'phoneVerification');
        }

        $data = $validator->validated();

        if (!$this->phoneVerification->verify($data['phone'], $data['verification_code'])) {
            return back()->withErrors([
                'verification_code' => __('Неверный или просроченный код. Попробуйте отправить SMS ещё раз.'),
            ], 'phoneVerification');
        }

        $user = $request->user();

        $user->phone = $data['phone'];
        $user->phone_verified_at = now();
        $user->save();

        return back()->with('phone_status', __('Номер телефона подтверждён.'));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
