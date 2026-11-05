<?php

use App\Support\AggregateClassName;

function classNameFor(string $relativePath): string
{
    $base = sys_get_temp_dir().'/aggregate-class-name-'.getmypid();
    @mkdir(dirname($base.'/'.$relativePath), 0777, true);
    touch($base.'/'.$relativePath);

    return AggregateClassName::fromFile(new SplFileInfo($base.'/'.$relativePath), $base);
}

test('a listener in an aggregate maps to the aggregate root namespace', function () {
    expect(classNameFor('src/Tasks/Listeners/NotifyAssignee.php'))
        ->toBe('Tasks\Listeners\NotifyAssignee');
});

test('a listener in app maps to the App namespace', function () {
    expect(classNameFor('app/Listeners/LogLogin.php'))->toBe('App\Listeners\LogLogin');
});
