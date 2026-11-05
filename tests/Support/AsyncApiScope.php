<?php

namespace Tests\Support;

use App\Support\AggregateClassName;
use App\Support\DomainEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Enforces the AsyncAPI scope rule (resources/docs/AGENTS.md, "AsyncAPI
 * scope"): AsyncAPI documents domain events only. Every channel address
 * must name a domain event, and every domain event must be documented.
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
     * @return list<string> the AsyncAPI specs in resources/docs/api
     */
    public static function specsIn(string $basePath): array
    {
        return array_values(array_merge(
            glob($basePath.'/resources/docs/api/*.asyncapi.yaml') ?: [],
            glob($basePath.'/resources/docs/api/*.asyncapi.yml') ?: [],
        ));
    }

    /**
     * @return list<string> the class names a spec documents as channels,
     *                      in `channels` and `components.channels`
     */
    public static function channelAddresses(string $specPath): array
    {
        $spec = self::parse($specPath);

        $channels = array_merge(
            self::channelsOf($spec['channels'] ?? []),
            self::channelsOf($spec['components']['channels'] ?? []),
        );

        return array_values(array_map(
            fn (array $channel, string $key): string => ltrim((string) ($channel['address'] ?? $key), '\\'),
            $channels,
            array_map('strval', array_keys($channels)),
        ));
    }

    /**
     * @return list<string> one message per rule the spec breaks
     */
    public static function violations(string $specPath): array
    {
        $violations = [];
        $version = (string) (self::parse($specPath)['asyncapi'] ?? '');

        if (! str_starts_with($version, '3.')) {
            $violations[] = basename($specPath).' must declare `asyncapi: 3.x`, found ['.$version.'].';
        }

        foreach (self::channelAddresses($specPath) as $address) {
            $violations = [...$violations, ...self::addressViolations($address)];
        }

        return $violations;
    }

    /**
     * Every class implementing DomainEvent under the PSR-4 roots that
     * composer.json maps to src/, found recursively.
     *
     * @return list<class-string<DomainEvent>>
     */
    public static function domainEventsIn(string $basePath): array
    {
        /** @var array{autoload: array{psr-4: array<string, string|list<string>>}} $composer */
        $composer = json_decode((string) file_get_contents($basePath.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);

        $directories = [];

        foreach ($composer['autoload']['psr-4'] as $paths) {
            foreach ((array) $paths as $path) {
                if (str_starts_with($path, 'src/') && is_dir($basePath.'/'.$path)) {
                    $directories[] = $basePath.'/'.$path;
                }
            }
        }

        if ($directories === []) {
            return [];
        }

        $events = [];

        foreach (Finder::create()->files()->name('*.php')->in($directories) as $file) {
            $class = AggregateClassName::fromFile(new SplFileInfo($file->getPathname()), $basePath);

            if (class_exists($class) && is_subclass_of($class, DomainEvent::class)) {
                $events[] = $class;
            }
        }

        return $events;
    }

    /**
     * @return list<string> placement rules a domain event class breaks
     */
    public static function placementViolations(string $class): array
    {
        $violations = [];

        if (! str_contains($class, '\\Events\\')) {
            $violations[] = "{$class} must live in an Events/ folder of its aggregate.";
        }

        if (! is_subclass_of($class, ShouldDispatchAfterCommit::class)) {
            $violations[] = "{$class} must implement ".ShouldDispatchAfterCommit::class.'.';
        }

        return $violations;
    }

    /**
     * @return list<string>
     */
    private static function addressViolations(string $address): array
    {
        foreach (self::FRAMEWORK_NAMESPACES as $namespace) {
            if (str_starts_with($address, $namespace)) {
                return ["{$address} is a framework or package event, not a domain event."];
            }
        }

        if (! class_exists($address)) {
            return ["{$address} is not a class."];
        }

        if (! is_subclass_of($address, DomainEvent::class) || (new ReflectionClass($address))->isInterface()) {
            return ["{$address} does not implement ".DomainEvent::class.'.'];
        }

        return self::placementViolations($address);
    }

    /**
     * @return array<string, mixed>
     */
    private static function parse(string $specPath): array
    {
        $spec = Yaml::parseFile($specPath);

        return is_array($spec) ? $spec : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function channelsOf(mixed $channels): array
    {
        if (! is_array($channels)) {
            return [];
        }

        return array_filter($channels, 'is_array');
    }
}
