<?php

declare(strict_types=1);

namespace App\OpenApi;

/**
 * Builds OpenAPI 3.0.3 specification for Infra Watch API.
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
                'version' => '2.0',
                'description' => 'API for server, service check and monitoring log management with JWT authentication.',
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
                    'service_checks' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/ServiceCheck'],
                        'description' => 'Service checks linked to this server (included in GET server/list)',
                    ],
                ],
            ],
            'ServerCreate' => [
                'type' => 'object',
                'required' => [
                    'name', 'ip_address', 'is_active', 'monitor_resources',
                    'cpu_total', 'ram_total', 'disk_total', 'check_interval_seconds',
                    'retention_days', 'cpu_alert_threshold', 'ram_alert_threshold',
                    'disk_alert_threshold', 'bandwidth_alert_threshold',
                    'alert_cpu_enabled', 'alert_ram_enabled', 'alert_disk_enabled', 'alert_bandwidth_enabled',
                ],
                'properties' => [
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'ip_address' => ['type' => 'string', 'format' => 'ipv4'],
                    'is_active' => ['type' => 'boolean'],
                    'monitor_resources' => ['type' => 'boolean'],
                    'cpu_total' => ['type' => 'number'],
                    'ram_total' => ['type' => 'number'],
                    'disk_total' => ['type' => 'number'],
                    'check_interval_seconds' => ['type' => 'integer'],
                    'last_check_at' => ['type' => 'string', 'nullable' => true],
                    'retention_days' => ['type' => 'integer'],
                    'cpu_alert_threshold' => ['type' => 'integer'],
                    'ram_alert_threshold' => ['type' => 'integer'],
                    'disk_alert_threshold' => ['type' => 'integer'],
                    'bandwidth_alert_threshold' => ['type' => 'integer'],
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
                    'meta' => ['$ref' => '#/components/schemas/PaginationMeta'],
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
                    'meta' => ['$ref' => '#/components/schemas/PaginationMeta'],
                ],
            ],
            'MonitoringLogServiceCheck' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'monitoring_log_id' => ['type' => 'integer'],
                    'service_check_id' => ['type' => 'integer'],
                    'is_running' => ['type' => 'boolean'],
                    'output_message' => ['type' => 'string', 'nullable' => true],
                    'created_at' => ['type' => 'string', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'MonitoringLog' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'server_id' => ['type' => 'integer'],
                    'checked_at' => ['type' => 'string', 'nullable' => true],
                    'is_up' => ['type' => 'boolean'],
                    'cpu_usage_percent' => ['type' => 'number', 'nullable' => true],
                    'ram_usage_percent' => ['type' => 'number', 'nullable' => true],
                    'disk_usage_percent' => ['type' => 'number', 'nullable' => true],
                    'bandwidth_usage_percent' => ['type' => 'number', 'nullable' => true],
                    'is_alert' => ['type' => 'boolean'],
                    'alert_type' => ['type' => 'string', 'nullable' => true],
                    'error_message' => ['type' => 'string', 'nullable' => true],
                    'sent_to_email' => ['type' => 'string', 'nullable' => true],
                    'created_at' => ['type' => 'string', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'nullable' => true],
                    'service_checks' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/MonitoringLogServiceCheck'],
                    ],
                ],
            ],
            'MonitoringLogCollection' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/MonitoringLog']],
                    'count' => ['type' => 'integer'],
                    'meta' => ['$ref' => '#/components/schemas/PaginationMeta'],
                ],
            ],
            'PaginationMeta' => [
                'type' => 'object',
                'properties' => [
                    'page' => ['type' => 'integer', 'example' => 1],
                    'per_page' => ['type' => 'integer', 'example' => 10],
                    'total' => ['type' => 'integer', 'example' => 42],
                    'total_pages' => ['type' => 'integer', 'example' => 5],
                    'has_next' => ['type' => 'boolean', 'example' => true],
                    'has_prev' => ['type' => 'boolean', 'example' => false],
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
                        ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 1, 'minimum' => 1]],
                        ['name' => 'per_page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 10, 'minimum' => 1, 'maximum' => 100]],
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
                'delete' => [
                    'summary' => 'Delete server',
                    'description' => 'Soft delete: marks the server as deleted (sets deleted_at). The record remains in the database; GET endpoints will not return it.',
                    'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                    'responses' => [
                        '200' => [
                            'description' => 'Server deleted',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean', 'example' => true],
                                            'message' => ['type' => 'string', 'example' => 'Server deleted'],
                                            'data' => ['type' => 'object', 'nullable' => true],
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
                    'parameters' => [
                        ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 1, 'minimum' => 1]],
                        ['name' => 'per_page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 10, 'minimum' => 1, 'maximum' => 100]],
                    ],
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
                'delete' => [
                    'summary' => 'Delete service check',
                    'description' => 'Soft delete: marks the service check as deleted (sets deleted_at). The record remains in the database; GET endpoints will not return it.',
                    'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                    'responses' => [
                        '200' => [
                            'description' => 'Service check deleted',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean', 'example' => true],
                                            'message' => ['type' => 'string', 'example' => 'Service check deleted'],
                                            'data' => ['type' => 'object', 'nullable' => true],
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
            '/api/servers/{serverId}/service-checks/available' => [
                'get' => [
                    'summary' => 'List service checks available to link (not yet linked to server)',
                    'parameters' => [
                        ['name' => 'serverId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 1, 'minimum' => 1]],
                        ['name' => 'per_page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 10, 'minimum' => 1, 'maximum' => 100]],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Available service checks',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ServiceCheck']],
                                                    'count' => ['type' => 'integer'],
                                                    'meta' => ['$ref' => '#/components/schemas/PaginationMeta'],
                                                ],
                                            ],
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
                        '409' => ['description' => 'Service check is already linked to this server', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
                'delete' => [
                    'summary' => 'Detach service check from server',
                    'description' => 'Soft delete: marks the link as deleted. The service check becomes available to link again (list available).',
                    'parameters' => [
                        ['name' => 'serverId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ['name' => 'serviceCheckId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Service check unlinked successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean', 'example' => true],
                                            'message' => ['type' => 'string', 'example' => 'Service check unlinked successfully'],
                                            'data' => ['type' => 'object', 'nullable' => true],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '404' => ['description' => 'Server, service check or link not found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
            ],
            '/api/monitoring-logs' => [
                'get' => [
                    'summary' => 'List monitoring logs',
                    'parameters' => [
                        ['name' => 'server_id', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer']],
                        ['name' => 'from', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ['name' => 'to', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ['name' => 'alerts_only', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['1', '0', 'true', 'false', 'on', 'off']]],
                        ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 1, 'minimum' => 1]],
                        ['name' => 'per_page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 50, 'minimum' => 1, 'maximum' => 200]],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Monitoring logs retrieved',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/MonitoringLogCollection'],
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
            '/api/monitoring-logs/{id}' => [
                'get' => [
                    'summary' => 'Get monitoring log details',
                    'parameters' => [
                        ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Monitoring log retrieved',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/MonitoringLog'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['description' => 'Unauthorized', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '404' => ['description' => 'Monitoring log not found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                        '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]],
                    ],
                ],
            ],
            '/api/servers/{serverId}/monitoring-logs' => [
                'get' => [
                    'summary' => 'List monitoring logs by server',
                    'parameters' => [
                        ['name' => 'serverId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 1, 'minimum' => 1]],
                        ['name' => 'per_page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 50, 'minimum' => 1, 'maximum' => 200]],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Monitoring logs retrieved',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/MonitoringLogCollection'],
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
            '/api/servers/{serverId}/monitoring-logs/dashboard' => [
                'get' => [
                    'summary' => 'List monitoring logs for dashboard',
                    'parameters' => [
                        ['name' => 'serverId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 1, 'minimum' => 1]],
                        ['name' => 'per_page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 50, 'minimum' => 1, 'maximum' => 200]],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Monitoring dashboard logs retrieved',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/MonitoringLogCollection'],
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
