<?php

use App\Models\User;

it('navigates the drawer from the dashboard to settings', function () {
    $this->actingAs(User::factory()->create());

    visit(route('dashboard'))
        ->assertVisible('@app-drawer')
        ->assertAttribute('[data-test="app-drawer"] a.v-list-item[href$="/dashboard"]', 'aria-current', 'page')
        ->click('Settings')
        ->assertRoute('profile.edit')
        ->assertSee('Manage your profile and account settings')
        ->assertNoJavaScriptErrors();
});

it('uses the bottom navigation on compact screens', function () {
    $this->actingAs(User::factory()->create());

    visit(route('dashboard'))
        ->on()->mobile()
        ->assertVisible('@app-bottom-nav')
        ->assertMissing('@app-drawer')
        ->assertNoJavaScriptErrors();
});

it('updates the profile and shows the saved toast', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    visit(route('profile.edit'))
        ->clear('name')
        ->type('name', 'Katherine Johnson')
        ->press('@update-profile-button')
        ->assertSee('Profile updated.')
        ->assertNoJavaScriptErrors();

    expect($user->refresh()->name)->toBe('Katherine Johnson');
});

it('switches to the dark theme from the appearance settings', function () {
    $this->actingAs(User::factory()->create());

    visit(route('appearance.edit'))
        ->click('Dark')
        ->assertScript("document.querySelector('.v-application').classList.contains('v-theme--dark')")
        ->refresh()
        ->assertScript("document.querySelector('.v-application').classList.contains('v-theme--dark')")
        ->assertNoJavaScriptErrors();
});
