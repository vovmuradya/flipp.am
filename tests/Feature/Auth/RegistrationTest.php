<?php

use App\Models\User;
use App\Services\PhoneVerificationService;
use Illuminate\Support\Str;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $this->mock(PhoneVerificationService::class, function ($mock) {
        $mock->shouldReceive('verify')->andReturn(true);
        $mock->shouldReceive('normalize')->andReturn('+15550001122');
    });

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+1 (555) 000-1122',
        'verification_code' => '123456',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard.index', absolute: false));
});

test('duplicate phone numbers are caught after normalization', function () {
    User::factory()->create([
        'phone' => '+15550001122',
    ]);

    $this->mock(PhoneVerificationService::class, function ($mock) {
        $mock->shouldReceive('verify')->andReturn(true);
        $mock->shouldReceive('normalize')->andReturnUsing(function (string $phone) {
            return Str::of($phone)->replaceMatches('/[^0-9+]/', '')->value();
        });
    });

    $response = $this->from('/register')->post('/register', [
        'name' => 'Second User',
        'email' => 'second@example.com',
        'phone' => '+1 (555) 000-1122',
        'verification_code' => '123456',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors('phone');
    $this->assertGuest();
});
