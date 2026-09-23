<?php

declare(strict_types=1);

/**
 * @return array<string, array<int, callable>>
 */
function &hook_registry(): array
{
    static $listeners = [];
    return $listeners;
}

function add_action(string $hook, callable $callback): void
{
    $registry = &hook_registry();
    $registry[$hook][] = $callback;
}

function do_action(string $hook, mixed ...$args): void
{
    $registry = &hook_registry();
    foreach ($registry[$hook] ?? [] as $callback) {
        $callback(...$args);
    }
}
