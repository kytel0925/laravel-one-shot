<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Tests\Support\Mailpit;

/*
 * Account flows that go through real email (SMTP → Mailpit) and Fortify's
 * security features, end to end in Chromium.
 */

it('resets a forgotten password through the emailed link', function () {
    $user = User::factory()->create(['email' => 'reset-'.Str::lower(Str::random(8)).'@example.com']);

    $page = visit(route('password.request'))
        ->type('email', $user->email)
        ->press('@email-password-reset-link-button');

    seeEventually($page, 'We have emailed your password reset link.')->assertNoJavaScriptErrors();

    $link = Mailpit::linkSentTo($user->email, '/reset-password/');

    visit($link)
        ->type('password', 'brand-new-password')
        ->type('password_confirmation', 'brand-new-password')
        ->press('@reset-password-button')
        ->assertRoute('login')
        ->type('email', $user->email)
        ->type('password', 'brand-new-password')
        ->press('@login-button')
        ->assertRoute('dashboard')
        ->assertNoJavaScriptErrors();

    Mailpit::deleteMessagesTo($user->email);
});

it('verifies the email address through the emailed link', function () {
    $user = User::factory()->unverified()->create(['email' => 'verify-'.Str::lower(Str::random(8)).'@example.com']);
    $this->actingAs($user);

    $page = visit(route('verification.notice'))
        ->press('Resend verification email');

    seeEventually($page, 'A new verification link has been sent')->assertNoJavaScriptErrors();

    visit(Mailpit::linkSentTo($user->email, '/email/verify/'))
        ->assertRoute('dashboard')
        ->assertNoJavaScriptErrors();

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();

    Mailpit::deleteMessagesTo($user->email);
});

it('completes the two-factor challenge with an authenticator code', function () {
    $google2fa = new Google2FA;
    $secret = $google2fa->generateSecretKey();

    $user = User::factory()->create();
    $user->forceFill([
        'two_factor_secret' => encrypt($secret),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    // The OTP boxes are drawn over one real input (.v-otp-input__input).
    visit(route('login'))
        ->type('email', $user->email)
        ->type('password', 'password')
        ->press('@login-button')
        ->assertRoute('two-factor.login')
        ->type('.v-otp-input__input', $google2fa->getCurrentOtp($secret))
        ->press('Continue')
        ->assertRoute('dashboard')
        ->assertNoJavaScriptErrors();
});

it('completes the two-factor challenge with a recovery code', function () {
    $user = User::factory()->withTwoFactor()->create();

    visit(route('login'))
        ->type('email', $user->email)
        ->type('password', 'password')
        ->press('@login-button')
        ->assertRoute('two-factor.login')
        ->click('login using a recovery code')
        ->type('recovery_code', 'recovery-code-1')
        ->press('Continue')
        ->assertRoute('dashboard')
        ->assertNoJavaScriptErrors();
});

it('confirms the password and changes it from the security settings', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = visit(route('security.edit'))
        ->assertRoute('password.confirm')
        ->type('password', 'password')
        ->press('@confirm-password-button')
        ->assertRoute('security.edit')
        ->type('current_password', 'password')
        ->type('password', 'another-new-password')
        ->type('password_confirmation', 'another-new-password')
        ->press('@update-password-button');

    seeEventually($page, 'Password updated.')->assertNoJavaScriptErrors();

    expect(Hash::check('another-new-password', $user->refresh()->password))->toBeTrue();
});

it('deletes the account after confirming the password', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    visit(route('profile.edit'))
        ->press('@delete-user-button')
        ->type('[role="dialog"] input[name="password"]', 'password')
        ->press('@confirm-delete-user-button')
        ->assertRoute('home')
        ->assertNoJavaScriptErrors();

    expect(User::find($user->id))->toBeNull();
});
