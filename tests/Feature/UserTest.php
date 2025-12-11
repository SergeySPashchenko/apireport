<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function (): void {
    $this->withoutExceptionHandling();

    $user = User::factory()->create(['email' => 'admin@admin.com']);

    Sanctum::actingAs($user, [], 'web');
});

test('it gets users list', function (): void {
    $users = User::factory()
        ->count(5)
        ->create();

    $response = $this->get(route('api.users.index'));

    $response->assertOk()->assertSee($users[0]->name);
});

test('it stores the user', function (): void {
    $data = User::factory()
        ->make()
        ->toArray();

    $data['password'] = Str::random('8');

    $response = $this->postJson(route('api.users.store'), $data);

    unset($data['password']);
    unset($data['email_verified_at']);
    unset($data['created_at']);
    unset($data['updated_at']);
    unset($data['deleted_at']);

    $this->assertDatabaseHas('users', $data);

    $response->assertStatus(201)->assertJsonFragment($data);
});

test('it updates the user', function (): void {
    $user = User::factory()->create();

    $data = [
        'name' => fake()->name(),
        'email' => fake()
            ->unique()
            ->safeEmail(),
    ];

    $data['password'] = Str::random('8');

    $response = $this->putJson(route('api.users.update', $user), $data);

    unset($data['password']);
    unset($data['email_verified_at']);
    unset($data['created_at']);
    unset($data['updated_at']);
    unset($data['deleted_at']);

    $data['id'] = $user->id;

    $this->assertDatabaseHas('users', $data);

    $response->assertStatus(200)->assertJsonFragment($data);
});

test('it deletes the user', function (): void {
    $user = User::factory()->create();

    $response = $this->deleteJson(route('api.users.destroy', $user));

    $this->assertSoftDeleted($user);

    $response->assertNoContent();
});
