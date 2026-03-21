<?php

declare(strict_types=1);

namespace App\OpenApi;

/**
 * Builds OpenAPI 3.0.3 specification for Part 1 API.
 */
final class OpenApiSpec
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Infra Watch API',
                'version' => '1.0',
                'description' => 'API for server and service check management with JWT authentication.',
            ],
            'servers' => [
                ['url' => '/'],
            ],
            'security' => [
                ['Bearer' => []],
            ],
            'components' => [
                'securitySchemes' => [
                    'Bearer' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                        'description' => 'JWT token from login endpoint',
                    ],
                ],
                'schemas' => $this->schemas(),
            ],
            'paths' => $this->paths(),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function schemas(): array
    {
        return [
            'LoginRequest' => [
                'type' => 'object',
                'required' => ['email', 'password'],
                'properties' => [
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string'],
                ],
            ],
            'LoginSuccessData' => [
                'type' => 'object',
                'properties' => [
                    'token' => ['type' => 'string'],
                    'user_id' => ['type' => 'integer'],
                    'email' => ['type' => 'string'],
                ],
            ],
            'SuccessResponse' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => true],
                    'message' => ['type' => 'string'],
                    'data' => ['type' => 'object'],
                ],
            ],
            'Server' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'ip_address' => ['type' => 'string'],
                    'is_active' => ['type' => 'boolean'],
                    'monitor_resources' => ['type' => 'boolean'],
                    'cpu_total' => ['type' => 'number', 'nullable' => true],
                    'ram_total' => ['type' => 'number', 'nullable' => true],
                    'disk_total' => ['type' => 'number', 'nullable' => true],
                    'check_interval_seconds' => ['type' => 'integer', 'nullable' => true],
                    'last_check_at' => ['type' => 'string', 'nullable' => true],
                    'retention_days' => ['type' => 'integer', 'nullable' => true],
                    'cpu_alert_threshold' => ['type' => 'number', 'nullable' => true],
                    'ram_alert_threshold' => ['type' => 'number', 'nullable' => true],
                    'disk_alert_threshold' => ['type' => 'number', 'nullable' => true],
                    'bandwidth_alert_threshold' => ['type' => 'number', 'nullable' => true],
                    'alert_cpu_enabled' => ['type' => 'boolean'],
                    'alert_ram_enabled' => ['type' => 'boolean'],
                    'alert_disk_enabled' => ['type' => 'boolean'],
                    'alert_bandwidth_enabled' => ['type' => 'boolean'],
                    'created_by' => ['type' => 'integer', 'nullable' => true],
                    'created_at' => ['type' => 'string', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'ServerCreate' => [
                'type' => 'object',
                'required' => ['name', 'ip_address'],
                'properties' => [
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'ip_address' => ['type' => 'string', 'format' => 'ipv4'],
                    'is_active' => ['type' => 'boolean'],
                    'monitor_resources' => ['type' => 'boolean'],
                    'cpu_total' => ['type' => 'number', 'nullable' => true],
                    'ram_total' => ['type' => 'number', 'nullable' => true],
                    'disk_total' => ['type' => 'number', 'nullable' => true],
                    'check_interval_seconds' => ['type' => 'integer', 'nullable' => true],
                    'last_check_at' => ['type' => 'string', 'nullable' => true],
                    'retention_days' => ['type' => 'integer', 'nullable' => true],
                    'cpu_alert_threshold' => ['type' => 'integer', 'nullable' => true],
                    'ram_alert_threshold' => ['type' => 'integer', 'nullable' => true],
                    'disk_alert_threshold' => ['type' => 'integer', 'nullable' => true],
                    'bandwidth_alert_threshold' => ['type' => 'integer', 'nullable' => true],
                    'alert_cpu_enabled' => ['type' => 'boolean'],
                    'alert_ram_enabled' => ['type' => 'boolean'],
                    'alert_disk_enabled' => ['type' => 'boolean'],
                    'alert_bandwidth_enabled' => ['type' => 'boolean'],
                ],
            ],
            'ServerUpdate' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'ip_address' => ['type' => 'string', 'format' => 'ipv4'],
                    'is_active' => ['type' => 'boolean'],
                    'monitor_resources' => ['type' => 'boolean'],
                    'cpu_total' => ['type' => 'number', 'nullable' => true],
                    'ram_total' => ['type' => 'number', 'nullable' => true],
                    'disk_total' => ['type' => 'number', 'nullable' => true],
                    'check_interval_seconds' => ['type' => 'integer', 'nullable' => true],
                    'last_check_at' => ['type' => 'string', 'nullable' => true],
                    'retention_days' => ['type' => 'integer', 'nullable' => true],
                    'cpu_alert_threshold' => ['type' => 'integer', 'nullable' => true],
                    'ram_alert_threshold' => ['type' => 'integer', 'nullable' => true],
                    'disk_alert_threshold' => ['type' => 'integer', 'nullable' => true],
                    'bandwidth_alert_threshold' => ['type' => 'integer', 'nullable' => true],
                    'alert_cpu_enabled' => ['type' => 'boolean'],
                    'alert_ram_enabled' => ['type' => 'boolean'],
                    'alert_disk_enabled' => ['type' => 'boolean'],
                    'alert_bandwidth_enabled' => ['type' => 'boolean'],
                ],
            ],
            'ServerCollection' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Server']],
                    'count' => ['type' => 'integer'],
                ],
            ],
            'ServiceCheck' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'slug' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'created_at' => ['type' => 'string', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'ServiceCheckCreate' => [
                'type' => 'object',
                'required' => ['name', 'slug'],
                'properties' => [
                    'name' => ['type' => 'string'],
                    'slug' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'ServiceCheckUpdate' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'slug' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'ServiceCheckCollection' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ServiceCheck']],
                    'count' => ['type' => 'integer'],
                ],
            ],
            'ErrorResponse' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false],
                    'message' => ['type' => 'string'],
                    'errors' => ['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]],
                ],
            ],
            'ValidationErrorResponse' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false],
                    'message' => ['type' => 'string'],
                    'errors' => ['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paths(): array
    {
        return [
            '/api/auth/login' => [
                'post' => [
                    'summary' => 'Login',
                    'description' => 'Authenticate with email and password, returns JWT token.',
                    'security' => [],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/LoginRequest'],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Login successful',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/LoginSuccessData'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['description' => 'Invalid credentials', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '422' => ['description' => 'Validation failed', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
            ],
            '/api/auth/logout' => [
                'post' => [
                    'summary' => 'Logout',
                    'description' => 'Client discards token. Returns success for consistency.',
                    'security' => [],
                    'responses' => [
                        '200' => [
                            'description' => 'Logout successful',
                            'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/SuccessResponse']]],
                        ],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
            ],
            '/api/servers' => [
                'post' => [
                    'summary' => 'Create server',
                    'requestBody' => [
                        'required' => true,
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ServerCreate']]],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'Server created',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/Server'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '400' => ['description' => 'Bad request', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '404' => ['description' => 'User not found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '422' => ['description' => 'Validation failed', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
                'get' => [
                    'summary' => 'List servers',
                    'parameters' => [
                        ['name' => 'name', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ['name' => 'is_active', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['1', '0', 'true', 'false', 'on', 'off']]],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Servers retrieved',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/ServerCollection'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
            ],
            '/api/servers/{id}' => [
                'put' => [
                    'summary' => 'Update server',
                    'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                    'requestBody' => [
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ServerUpdate']]],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Server updated',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/Server'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '400' => ['description' => 'Bad request', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '404' => ['description' => 'Server not found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '422' => ['description' => 'Validation failed', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
                'get' => [
                    'summary' => 'Get server by id',
                    'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                    'responses' => [
                        '200' => [
                            'description' => 'Server retrieved',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/Server'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '404' => ['description' => 'Server not found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
            ],
            '/api/service-checks' => [
                'post' => [
                    'summary' => 'Create service check',
                    'requestBody' => [
                        'required' => true,
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ServiceCheckCreate']]],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'Service check created',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/ServiceCheck'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '400' => ['description' => 'Bad request', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '422' => ['description' => 'Validation failed', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
                'get' => [
                    'summary' => 'List service checks',
                    'responses' => [
                        '200' => [
                            'description' => 'Service checks retrieved',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/ServiceCheckCollection'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
            ],
            '/api/service-checks/{id}' => [
                'put' => [
                    'summary' => 'Update service check',
                    'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                    'requestBody' => [
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ServiceCheckUpdate']]],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Service check updated',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/ServiceCheck'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '404' => ['description' => 'Service check not found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '422' => ['description' => 'Validation failed', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
                'get' => [
                    'summary' => 'Get service check by id',
                    'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                    'responses' => [
                        '200' => [
                            'description' => 'Service check retrieved',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/ServiceCheck'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '404' => ['description' => 'Service check not found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
            ],
            '/api/service-checks/slug/{slug}' => [
                'get' => [
                    'summary' => 'Get service check by slug',
                    'parameters' => [['name' => 'slug', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']]],
                    'responses' => [
                        '200' => [
                            'description' => 'Service check retrieved',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/ServiceCheck'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '404' => ['description' => 'Service check not found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
            ],
            '/api/servers/{serverId}/service-checks/{serviceCheckId}' => [
                'post' => [
                    'summary' => 'Attach service check to server',
                    'parameters' => [
                        ['name' => 'serverId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ['name' => 'serviceCheckId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Service check linked to server',
                            'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/SuccessResponse']]],
                        ],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '404' => ['description' => 'Server or service check not found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
            ],
            '/api/openapi.json' => [
                'get' => [
                    'summary' => 'OpenAPI specification',
                    'description' => 'Returns the OpenAPI 3.0.3 specification for this API.',
                    'security' => [],
                    'responses' => [
                        '200' => [
                            'description' => 'OpenAPI specification',
                            'content' => ['application/json' => ['schema' => ['type' => 'object']]],
                        ],
                    ],
                ],
            ],
        ];
    }
}
