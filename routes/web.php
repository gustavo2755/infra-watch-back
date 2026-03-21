<?php

declare(strict_types=1);

if (!function_exists('swagger_ui_html')) {
    function swagger_ui_html(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infra Watch API - Documentation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "/api/openapi.json",
                dom_id: "#swagger-ui",
                deepLinking: true,
                presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
                layout: "StandaloneLayout"
            });
        };
    </script>
</body>
</html>
HTML;
    }
}

/**
 * @return array<int, array{method: string, path: string, handler: callable}>
 */
return function (): array {
    return [
        ['method' => 'GET', 'path' => '/docs', 'handler' => function (): void {
            header('Content-Type: text/html; charset=UTF-8');
            echo swagger_ui_html();
        }],
        ['method' => 'GET', 'path' => '/', 'handler' => function (): void {
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Infra Watch API - use /api/* endpoints';
        }],
    ];
};
