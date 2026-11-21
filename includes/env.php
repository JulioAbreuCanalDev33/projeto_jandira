<?php
declare(strict_types=1);

/**
 * Carregador minimalista de variáveis de ambiente.
 *
 * Lê um arquivo .env no formato KEY=VALUE, sem dependências externas.
 * Valores já definidos no ambiente do processo têm prioridade e não
 * são sobrescritos pelo arquivo.
 */
function load_env(string $path): void
{
    static $loaded = [];

    $realPath = realpath($path);
    $path = $realPath !== false ? $realPath : $path;

    if (isset($loaded[$path]) || !is_file($path) || !is_readable($path)) {
        return;
    }

    $loaded[$path] = true;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_starts_with($line, 'export ')) {
            $line = trim(substr($line, 7));
        }

        $separator = strpos($line, '=');

        if ($separator === false) {
            continue;
        }

        $key = trim(substr($line, 0, $separator));
        $value = trim(substr($line, $separator + 1));

        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        if ($value !== '' && strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];

            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);

    if ($value === false) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }

    if ($value === null || $value === '') {
        return $default;
    }

    return (string) $value;
}

function env_bool(string $key, bool $default = false): bool
{
    $value = env($key);

    if ($value === null) {
        return $default;
    }

    return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
}
