<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests from protected dashboards to login', function (): void {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

    $this->get(route('wali.dashboard'))
        ->assertRedirect(route('login'));
});

it('does not provide public registration', function (): void {
    $this->get('/register')->assertNotFound();
});

it('logs an admin in and redirects to the admin dashboard', function (): void {
    $admin = User::factory()->admin()->create();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

it('logs a wali in and redirects to the wali dashboard', function (): void {
    $wali = User::factory()->wali()->create();

    $this->post(route('login.store'), [
        'email' => $wali->email,
        'password' => 'password',
    ])->assertRedirect(route('wali.dashboard'));

    $this->assertAuthenticatedAs($wali);
});

it('rejects invalid login credentials with an Indonesian message', function (): void {
    $user = User::factory()->create();

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'email' => 'Email atau kata sandi tidak sesuai.',
        ]);

    $this->assertGuest();
});

it('logs an authenticated user out', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('forbids a wali from accessing the admin dashboard', function (): void {
    $wali = User::factory()->wali()->create();

    $this->actingAs($wali)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('forbids an admin from accessing the wali dashboard', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('wali.dashboard'))
        ->assertForbidden();
});
