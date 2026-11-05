<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Octane\ApplicationFactory;
use Laravel\Octane\Contracts\Client;
use Laravel\Octane\Octane;
use Laravel\Octane\Testing\Fakes\FakeClient;
use Laravel\Octane\Testing\Fakes\FakeWorker;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends the given requests through one Octane worker, the way FrankenPHP's
 * worker mode reuses a booted application, and returns the responses.
 *
 * The worker runs the test's own application as its base: Octane clones it
 * into a sandbox for every request, exactly as in production, and rows
 * created inside the RefreshDatabase transaction stay visible.
 *
 * @param  list<Request>  $requests
 * @return list<Response>
 */
function runThroughOneOctaneWorker(array $requests): array
{
    $app = app();
    (new ApplicationFactory(base_path()))->warm($app, Octane::defaultServicesToWarm());

    $client = new FakeClient($requests);
    $worker = new FakeWorker(
        Mockery::mock(ApplicationFactory::class, ['createApplication' => $app]),
        $client,
    );
    $app->bind(Client::class, fn () => $client);

    $worker->boot();
    $worker->run();

    expect($client->errors)->toBe([]);

    return $client->responses;
}

/**
 * @return array<string, mixed>
 */
function inertiaProps(Response $response): array
{
    return json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR)['props'];
}

test('a signed-in user does not leak into the next request on the same worker', function () {
    $user = User::factory()->create();

    [$login, $nextGuest] = runThroughOneOctaneWorker([
        Request::create(route('login.store'), 'POST', [
            'email' => $user->email,
            'password' => 'password',
        ]),
        Request::create(route('dashboard')),
    ]);

    expect($login->getStatusCode())->toBe(302)
        ->and($login->headers->get('Location'))->toBe(route('dashboard'))
        ->and($nextGuest->getStatusCode())->toBe(302)
        ->and($nextGuest->headers->get('Location'))->toBe(route('login'));
});

test('request cookies do not leak into shared Inertia props of the next request', function () {
    $inertia = [
        'HTTP_X-Inertia' => 'true',
        'HTTP_X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()),
        'HTTP_X-Requested-With' => 'XMLHttpRequest',
    ];

    [$dark, $default] = runThroughOneOctaneWorker([
        Request::create(route('login'), 'GET', cookies: ['appearance' => 'dark'], server: $inertia),
        Request::create(route('login'), 'GET', server: $inertia),
    ]);

    expect(inertiaProps($dark)['appearance'])->toBe('dark')
        ->and(inertiaProps($default)['appearance'])->toBe('system')
        ->and(inertiaProps($default)['auth']['user'])->toBeNull();
});
