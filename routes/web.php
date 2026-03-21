<?php

declare(strict_types=1);

/**
 * Web routes - minimal for Part 1.
 *
 * @return array<int, array{method: string, path: string, handler: callable}>
 */
return function (): array {
    return [
        ['method' => 'GET', 'path' => '/', 'handler' => function () {
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Infra Watch API - use /api/* endpoints';
        }],
    ];
};
