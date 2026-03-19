<?php

declare(strict_types=1);

namespace App\Requests;

use App\Exceptions\ValidationException;

/**
 * Request validation for updating a server (patch-like, partial updates allowed).
 */
final class UpdateServerRequest
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        $errors = [];
        $validated = [];

        $optionalString = ['name', 'description', 'ip_address', 'last_check_at'];

        foreach ($optionalString as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $this->validateString($data, $key, $key === 'ip_address', $errors, $validated);
        }

        $optionalBool = ['is_active', 'monitor_resources', 'alert_cpu_enabled', 'alert_ram_enabled', 'alert_disk_enabled', 'alert_bandwidth_enabled'];

        foreach ($optionalBool as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $this->validateBool($data, $key, $errors, $validated);
        }

        $optionalNumeric = ['cpu_total', 'ram_total', 'disk_total'];

        foreach ($optionalNumeric as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $this->validateNumeric($data, $key, $errors, $validated);
        }

        $optionalInt = ['check_interval_seconds', 'retention_days', 'cpu_alert_threshold', 'ram_alert_threshold', 'disk_alert_threshold', 'bandwidth_alert_threshold', 'created_by'];

        foreach ($optionalInt as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $this->validateInt($data, $key, $errors, $validated);
        }

        if (array_key_exists('last_check_at', $data)) {
            $this->validateDateTime($data, 'last_check_at', $errors, $validated);
        }

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
    private function validateString(array $data, string $key, bool $isIp, array &$errors, array &$validated): void
    {
        $val = $data[$key];

        if ($val === null || $val === '') {
            $validated[$key] = $key === 'description' ? null : '';
            return;
        }

        if (!is_string($val)) {
            $errors[$key] = [ucfirst(str_replace('_', ' ', $key)) . ' must be a string'];
            return;
        }

        if ($isIp && !filter_var($val, FILTER_VALIDATE_IP)) {
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
    private function validateBool(array $data, string $key, array &$errors, array &$validated): void
    {
        $val = $data[$key];

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
    private function validateNumeric(array $data, string $key, array &$errors, array &$validated): void
    {
        $val = $data[$key];

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
    private function validateInt(array $data, string $key, array &$errors, array &$validated): void
    {
        $val = $data[$key];

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
    private function validateDateTime(array $data, string $key, array &$errors, array &$validated): void
    {
        $val = $data[$key];

        if ($val === null || $val === '') {
            $validated[$key] = null;
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
