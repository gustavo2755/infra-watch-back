<?php

declare(strict_types=1);

namespace App\Requests;

use App\Exceptions\ValidationException;

/**
 * Request validation for creating a server.
 */
final class StoreServerRequest
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        if (array_is_list($data)) {
            throw new ValidationException('Invalid payload', ['payload' => ['Payload must be an object with named fields']]);
        }

        $errors = [];
        $validated = [];

        $this->requireString($data, 'name', true, $errors, $validated);
        $this->requireString($data, 'description', false, $errors, $validated);
        $this->requireIpAddress($data, 'ip_address', $errors, $validated);
        $this->requireBool($data, 'is_active', $errors, $validated);
        $this->requireBool($data, 'monitor_resources', $errors, $validated);
        $this->requireNumeric($data, 'cpu_total', $errors, $validated);
        $this->requireNumeric($data, 'ram_total', $errors, $validated);
        $this->requireNumeric($data, 'disk_total', $errors, $validated);
        $this->requireInt($data, 'check_interval_seconds', $errors, $validated);
        $this->requireDateTime($data, 'last_check_at', false, $errors, $validated);
        $this->requireInt($data, 'retention_days', $errors, $validated);
        $this->requireInt($data, 'cpu_alert_threshold', $errors, $validated);
        $this->requireInt($data, 'ram_alert_threshold', $errors, $validated);
        $this->requireInt($data, 'disk_alert_threshold', $errors, $validated);
        $this->requireInt($data, 'bandwidth_alert_threshold', $errors, $validated);
        $this->requireBool($data, 'alert_cpu_enabled', $errors, $validated);
        $this->requireBool($data, 'alert_ram_enabled', $errors, $validated);
        $this->requireBool($data, 'alert_disk_enabled', $errors, $validated);
        $this->requireBool($data, 'alert_bandwidth_enabled', $errors, $validated);

        if ($errors !== []) {
            throw new ValidationException('Validation failed', $errors);
        }

        return $validated;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $errors
     * @param array<string, mixed> $validated
     */
    private function requireString(array $data, string $key, bool $required, array &$errors, array &$validated): void
    {
        $val = $data[$key] ?? null;

        if ($val === null || $val === '') {
            if ($required) {
                $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' is required'];
            }

            return;
        }

        if (!is_string($val)) {
            $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' must be a string'];
            return;
        }

        $validated[$key] = $val;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $errors
     * @param array<string, mixed> $validated
     */
    private function requireIpAddress(array $data, string $key, array &$errors, array &$validated): void
    {
        $val = $data[$key] ?? null;

        if ($val === null || $val === '') {
            $errors[$key] = ['IP address is required'];
            return;
        }

        if (!is_string($val)) {
            $errors[$key] = ['IP address must be a string'];
            return;
        }

        if (!filter_var($val, FILTER_VALIDATE_IP)) {
            $errors[$key] = ['IP address format is invalid'];
            return;
        }

        $validated[$key] = $val;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $errors
     * @param array<string, mixed> $validated
     */
    private function requireBool(array $data, string $key, array &$errors, array &$validated): void
    {
        $val = $data[$key] ?? null;

        if ($val === null && !array_key_exists($key, $data)) {
            $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' is required'];
            return;
        }

        if (is_bool($val)) {
            $validated[$key] = $val;
            return;
        }

        if (in_array($val, [1, 0, '1', '0', 'true', 'false', 'on', 'off'], true)) {
            $validated[$key] = in_array($val, [true, 1, '1', 'true', 'on'], true);
            return;
        }

        $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' must be a boolean'];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $errors
     * @param array<string, mixed> $validated
     */
    private function requireNumeric(array $data, string $key, array &$errors, array &$validated): void
    {
        $val = $data[$key] ?? null;

        if ($val === null && !array_key_exists($key, $data)) {
            $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' is required'];
            return;
        }

        if (is_int($val) || is_float($val)) {
            $validated[$key] = (float) $val;
            return;
        }

        if (is_string($val) && is_numeric($val)) {
            $validated[$key] = (float) $val;
            return;
        }

        $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' must be a number'];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $errors
     * @param array<string, mixed> $validated
     */
    private function requireInt(array $data, string $key, array &$errors, array &$validated): void
    {
        $val = $data[$key] ?? null;

        if ($val === null && !array_key_exists($key, $data)) {
            $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' is required'];
            return;
        }

        if (is_int($val)) {
            $validated[$key] = $val;
            return;
        }

        if (is_string($val) && ctype_digit($val)) {
            $validated[$key] = (int) $val;
            return;
        }

        if (is_float($val) && $val == (int) $val) {
            $validated[$key] = (int) $val;
            return;
        }

        $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' must be an integer'];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $errors
     * @param array<string, mixed> $validated
     */
    private function requireDateTime(array $data, string $key, bool $required, array &$errors, array &$validated): void
    {
        $val = $data[$key] ?? null;

        if ($val === null || $val === '') {
            if ($required) {
                $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' is required'];
            } else {
                $validated[$key] = null;
            }

            return;
        }

        if (!is_string($val)) {
            $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' must be a string'];
            return;
        }

        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $val)
            ?: \DateTime::createFromFormat(\DateTime::ATOM, $val)
            ?: \DateTime::createFromFormat(\DateTime::RFC3339, $val);

        if ($dt === false) {
            $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' must be a valid datetime format'];
            return;
        }

        $validated[$key] = $val;
    }
}
