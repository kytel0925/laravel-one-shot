<?php

namespace Tests\Support;

use App\Support\DomainEvent;
use Symfony\Component\Yaml\Yaml;

/**
 * Enforces the AsyncAPI scope rule (resources/docs/AGENTS.md): AsyncAPI
 * documents domain events only. A channel address must name a class that
 * implements App\Support\DomainEvent, and every domain event must be
 * documented.
 */
final class AsyncApiScope
{
    /**
     * Namespaces of framework and package events, which never belong in
     * AsyncAPI even when the application listens to them.
     *
     * @var list<string>
     */
    private const FRAMEWORK_NAMESPACES = [
        'Illuminate\\',
        'Laravel\\',
        'Inertia\\',
        'Symfony\\',
        'Livewire\\',
        'VladimirYuldashev\\',
    ];

    /**
     * @return list<string> the class names a spec documents as channels
     */
    public static function channelAddresses(string $specPath): array
    {
        /** @var array{channels?: array<string, array{address?: string|null}>} $spec */
        $spec = Yaml::parseFile($specPath);

        return array_values(array_map(
            fn (array $channel, string $key): string => ltrim((string) ($channel['address'] ?? $key), '\\'),
            $spec['channels'] ?? [],
            array_keys($spec['channels'] ?? []),
        ));
    }

    /**
     * @return list<string> one message per channel that is not a domain event
     */
    public static function violations(string $specPath): array
    {
        $violations = [];

        foreach (self::channelAddresses($specPath) as $address) {
            foreach (self::FRAMEWORK_NAMESPACES as $namespace) {
                if (str_starts_with($address, $namespace)) {
                    $violations[] = "{$address} is a framework or package event, not a domain event.";

                    continue 2;
                }
            }

            if (! class_exists($address)) {
                $violations[] = "{$address} is not a class.";
            } elseif (! is_subclass_of($address, DomainEvent::class)) {
                $violations[] = "{$address} does not implement ".DomainEvent::class.'.';
            }
        }

        return $violations;
    }

    /**
     * Domain event classes found in `src/{Aggregate}/Events/`, using the
     * PSR-4 roots registered in composer.json.
     *
     * @return list<class-string<DomainEvent>>
     */
    public static function domainEventsIn(string $basePath): array
    {
        /** @var array{autoload: array{psr-4: array<string, string>}} $composer */
        $composer = json_decode((string) file_get_contents($basePath.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);

        $events = [];

        foreach ($composer['autoload']['psr-4'] as $namespace => $directory) {
            if (! str_starts_with($directory, 'src/')) {
                continue;
            }

            foreach (glob($basePath.'/'.rtrim($directory, '/').'/Events/*.php') ?: [] as $file) {
                $class = $namespace.'Events\\'.basename($file, '.php');

                if (class_exists($class) && is_subclass_of($class, DomainEvent::class)) {
                    $events[] = $class;
                }
            }
        }

        return $events;
    }
}
