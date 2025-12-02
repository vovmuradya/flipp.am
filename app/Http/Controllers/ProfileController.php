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
            'forcePhoneEdit' => $request->boolean('phone_edit'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        if ($fullName === '') {
            $fullName = $request->user()->name;
        }

        $user = $request->user();
        $user->name = $fullName;
        $user->email = $data['email'];

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars/' . $user->id, 'public');

            // удалить старый аватар, если он был сохранён на диске
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $avatarPath;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function verifyPhone(Request $request): RedirectResponse
    {
        $normalizedPhone = $this->phoneVerification->normalizeArmenian((string) $request->input('phone', ''));

        if (!$normalizedPhone) {
            return back()->withErrors([
                'phone' => __('Введите армянский номер телефона в формате +374 XX XXX XXX.'),
            ], 'phoneVerification')->withInput();
        }

        $validator = Validator::make(
            ['phone' => $normalizedPhone],
            [
                'phone' => [
                    'required',
                    'regex:/^\\+374\\d{8}$/',
                    Rule::unique('users', 'phone')->ignore($request->user()->id),
                ],
            ],
            [],
            [
                'phone' => __('Телефон'),
            ]
        );

        if ($validator->fails()) {
            return back()->withErrors($validator, 'phoneVerification')->withInput();
        }

        $user = $request->user();
        $user->phone = $normalizedPhone;
        $user->phone_verified_at = null;
        $user->save();

        return back()->with('phone_status', __('Номер телефона сохранён.'));
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
