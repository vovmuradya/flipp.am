<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '077 123 456',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard.index', absolute: false));
    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->first();
    $this->assertNotNull($user);
    $this->assertSame('+37477123456', $user->phone);
    $this->assertNull($user->phone_verified_at);
});

test('duplicate phone numbers are caught after normalization', function () {
    User::factory()->create([
        'phone' => '+37477123456',
    ]);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Second User',
        'email' => 'second@example.com',
        'phone' => '077 123 456',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors('phone');
    $this->assertGuest();
});
