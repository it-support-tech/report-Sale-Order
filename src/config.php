<?php

// Load .env (simple parser, no Composer dependency)
$envPath = __DIR__ . '/../.env';
if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (getenv($key) === false) {
            putenv("$key=$value");
        }
    }
}

function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

return [
    'db' => [
        'host' => env('DB_HOST', 'db'),
        'port' => env('DB_PORT', '5432'),
        'name' => env('DB_NAME', 'reportdn'),
        'user' => env('DB_USER', 'reportdn'),
        'pass' => env('DB_PASS', 'reportdn'),
    ],
];
