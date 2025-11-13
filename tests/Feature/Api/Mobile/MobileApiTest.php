<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['scout.driver' => 'null']);
});

function mobileCategory(): Category
{
    return Category::create([
        'name' => ['ru' => 'Авто', 'en' => 'Cars'],
        'slug' => 'auto-' . Str::random(5),
        'is_active' => true,
    ]);
}

it('allows mobile login with email', function () {
    $user = User::factory()->create([
        'email' => 'driver@example.com',
        'password' => Hash::make('secret-pass'),
    ]);

    $response = $this->postJson('/api/mobile/auth/login', [
        'login' => 'driver@example.com',
        'password' => 'secret-pass',
        'device_name' => 'ios-sim',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.id', $user->id);
});

it('creates listing via mobile endpoint', function () {
    $user = User::factory()->create([
        'phone' => '+37499111222',
        'phone_verified_at' => now(),
    ]);

    $category = mobileCategory();

    Sanctum::actingAs($user);

    $payload = [
        'title' => 'BMW X5',
        'description' => str_repeat('Описание ', 5),
        'price' => 23000,
        'currency' => 'USD',
        'category_id' => $category->id,
        'listing_type' => 'vehicle',
        'vehicle' => [
            'make' => 'BMW',
            'model' => 'X5',
            'year' => 2019,
        ],
    ];

    $response = $this->postJson('/api/mobile/listings', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'BMW X5')
        ->assertJsonPath('data.price.amount', 23000);

    $this->assertDatabaseHas('listings', [
        'title' => 'BMW X5',
        'user_id' => $user->id,
    ]);
});

it('sends chat messages and triggers push notification', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $category = mobileCategory();

    $listing = Listing::create([
        'user_id' => $seller->id,
        'category_id' => $category->id,
        'title' => 'Audi A6',
        'slug' => 'audi-a6-' . Str::random(5),
        'description' => 'Audi description',
        'price' => 18000,
        'currency' => 'USD',
        'status' => 'active',
        'language' => 'ru',
        'listing_type' => 'vehicle',
    ]);

    Sanctum::actingAs($buyer);

    $this->mock(PushNotificationService::class)
        ->shouldReceive('sendToUser')
        ->once();

    $response = $this->postJson("/api/mobile/listings/{$listing->id}/messages", [
        'body' => 'Здравствуйте, интересует машина',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.body', 'Здравствуйте, интересует машина')
        ->assertJsonPath('data.sender.id', $buyer->id)
        ->assertJsonPath('data.receiver.id', $seller->id);

    $this->assertDatabaseHas('messages', [
        'listing_id' => $listing->id,
        'sender_id' => $buyer->id,
        'receiver_id' => $seller->id,
        'body' => 'Здравствуйте, интересует машина',
    ]);
});
