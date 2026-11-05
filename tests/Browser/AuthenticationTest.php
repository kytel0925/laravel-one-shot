<?php

use App\Models\User;

/*
 * End-to-end flows of the starter kit pages in real Chromium (Pest browser
 * plugin + Playwright), against the Docker Compose services.
 */

it('shows the welcome page to guests', function () {
    visit(route('home'))
        ->assertTitle('Welcome - '.config('app.name'))
        ->assertSee("Let's get started")
        ->assertSeeLink('Log in')
        ->assertSeeLink('Register')
        ->assertNoJavaScriptErrors();
});

it('sends guests from the dashboard to the login page', function () {
    visit(route('dashboard'))
        ->assertRoute('login')
        ->assertSee('Log in to your account')
        ->assertNoJavaScriptErrors();
});

it('logs a user in and out', function () {
    $user = User::factory()->create(['name' => 'Ada Lovelace']);

    visit(route('login'))
        ->type('email', $user->email)
        ->type('password', 'password')
        ->press('@login-button')
        ->assertRoute('dashboard')
        ->assertSee('Welcome back, Ada')
        ->assertNoJavaScriptErrors()
        ->click('@sidebar-menu-button')
        ->click('@logout-button')
        ->assertRoute('home')
        ->assertSeeLink('Log in')
        ->assertNoJavaScriptErrors();

    $this->assertGuest();
});

it('rejects wrong credentials with a field error', function () {
    $user = User::factory()->create();

    visit(route('login'))
        ->type('email', $user->email)
        ->type('password', 'wrong-password')
        ->press('@login-button')
        ->assertRoute('login')
        ->assertSee('These credentials do not match our records.')
        ->assertNoJavaScriptErrors();

    $this->assertGuest();
});

it('registers a new account and asks to verify the email', function () {
    visit(route('register'))
        ->type('name', 'Grace Hopper')
        ->type('email', 'grace@example.com')
        ->type('password', 'correct-horse-battery')
        ->type('password_confirmation', 'correct-horse-battery')
        ->press('@register-user-button')
        ->assertRoute('verification.notice')
        ->assertSee('Email verification')
        ->assertNoJavaScriptErrors();

    expect(User::where('email', 'grace@example.com')->exists())->toBeTrue();
});
