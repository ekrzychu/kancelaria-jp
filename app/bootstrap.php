<?php
declare(strict_types=1);

const APP_ROOT = __DIR__;
const PROJECT_ROOT = __DIR__ . '/..';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

function data_file(string $name): array
{
    $path = APP_ROOT . '/data/' . $name . '.json';
    if (!is_file($path)) {
        throw new RuntimeException("Missing data file: {$name}.json");
    }

    $json = file_get_contents($path);
    if ($json === false) {
        throw new RuntimeException("Unable to read data file: {$name}.json");
    }

    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        throw new RuntimeException("Invalid data file: {$name}.json");
    }

    return $data;
}

$site = data_file('site');
$services = data_file('services');
$team = data_file('team');
$articles = data_file('articles');

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = '/' . ltrim(preg_replace('#/+#', '/', $path) ?: '/', '/');

    return $path === '/' ? '/' : rtrim($path, '/') . '/';
}

function page_title(string $title, array $site): string
{
    return $title . ' — ' . ($site['name'] ?? 'Kancelaria');
}

function canonical_url(array $site): string
{
    $baseUrl = rtrim((string)($site['url'] ?? ''), '/');

    return $baseUrl . current_path();
}

function find_by_slug(array $items, string $slug): ?array
{
    foreach ($items as $item) {
        if (($item['slug'] ?? null) === $slug) {
            return $item;
        }
    }

    return null;
}

function text_length(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    return preg_match_all('/./us', $value, $matches) ?: 0;
}

function text_lower(string $value): string
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($value, 'UTF-8');
    }

    return strtr(strtolower($value), [
        'Ą' => 'ą',
        'Ć' => 'ć',
        'Ę' => 'ę',
        'Ł' => 'ł',
        'Ń' => 'ń',
        'Ó' => 'ó',
        'Ś' => 'ś',
        'Ź' => 'ź',
        'Ż' => 'ż',
    ]);
}

function text_upper(string $value): string
{
    if (function_exists('mb_strtoupper')) {
        return mb_strtoupper($value, 'UTF-8');
    }

    return strtr(strtoupper($value), [
        'ą' => 'Ą',
        'ć' => 'Ć',
        'ę' => 'Ę',
        'ł' => 'Ł',
        'ń' => 'Ń',
        'ó' => 'Ó',
        'ś' => 'Ś',
        'ź' => 'Ź',
        'ż' => 'Ż',
    ]);
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }

        preg_match('/^./u', $part, $firstCharacter);
        $out .= text_upper($firstCharacter[0] ?? '');

        if (text_length($out) >= 2) {
            break;
        }
    }

    return $out ?: 'JP';
}

function icon(string $name, string $class = 'utility-icon'): string
{
    $safe = preg_replace('/[^a-z0-9-]/', '', strtolower($name));
    $path = PROJECT_ROOT . '/public/assets/icons/' . $safe . '.svg';
    if (!is_file($path)) {
        return '';
    }

    $svg = file_get_contents($path) ?: '';

    return preg_replace('/<svg\b/', '<svg class="' . e($class) . '" aria-hidden="true" focusable="false"', $svg, 1) ?: '';
}

function is_active(string $url): string
{
    $current = current_path();
    $target = rtrim($url, '/') . '/';

    return str_starts_with($current, $target) ? ' is-active' : '';
}

function is_current_section(string $url): bool
{
    $target = rtrim($url, '/') . '/';

    return str_starts_with(current_path(), $target);
}

function render(string $template, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require APP_ROOT . '/templates/header.php';
    require APP_ROOT . '/pages/' . $template . '.php';
    require APP_ROOT . '/templates/footer.php';
}

function storage_dir(): string
{
    return PROJECT_ROOT . '/storage';
}

function ensure_storage_dir(): bool
{
    $dir = storage_dir();

    return is_dir($dir) || @mkdir($dir, 0755, true) || is_dir($dir);
}

function is_contact_rate_limited(int $limit = 3, int $windowSeconds = 3600): bool
{
    if (!ensure_storage_dir()) {
        return false;
    }

    $path = storage_dir() . '/contact-rate-limit.json';
    $now = time();
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $key = hash('sha256', $ip);
    $handle = @fopen($path, 'c+');
    if ($handle === false || !flock($handle, LOCK_EX)) {
        if (is_resource($handle)) {
            fclose($handle);
        }

        return false;
    }

    try {
        $json = stream_get_contents($handle);
        $decoded = json_decode($json ?: '{}', true);
        $data = is_array($decoded) ? $decoded : [];

        foreach ($data as $rateKey => $timestamps) {
            if (!is_array($timestamps)) {
                unset($data[$rateKey]);
                continue;
            }

            $data[$rateKey] = array_values(array_filter(
                $timestamps,
                fn ($timestamp) => is_int($timestamp) && $timestamp >= $now - $windowSeconds
            ));

            if ($data[$rateKey] === []) {
                unset($data[$rateKey]);
            }
        }

        $attempts = $data[$key] ?? [];
        $limited = count($attempts) >= $limit;
        if (!$limited) {
            $attempts[] = $now;
            $data[$key] = $attempts;
        }

        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL);
        fflush($handle);

        return $limited;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function start_form_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'path' => '/',
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_form_session();
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function valid_csrf_token(string $token): bool
{
    start_form_session();
    $expected = $_SESSION['csrf_token'] ?? '';

    return is_string($expected) && $expected !== '' && hash_equals($expected, $token);
}

function handle_contact_submission(): ?array
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return null;
    }

    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));
    $website = trim((string)($_POST['website'] ?? ''));
    $csrf = (string)($_POST['csrf_token'] ?? '');
    $fields = compact('name', 'email', 'phone', 'message');

    if ($website !== '') {
        return ['ok' => true, 'message' => 'Dziękujemy. Wiadomość została zapisana po stronie serwera.'];
    }

    if (!valid_csrf_token($csrf)) {
        return ['ok' => false, 'message' => 'Sesja formularza wygasła. Odśwież stronę i spróbuj ponownie.', 'fields' => $fields];
    }
    if ($name === '' || $email === '' || $message === '') {
        return ['ok' => false, 'message' => 'Uzupełnij imię i nazwisko, adres e-mail oraz krótki opis sprawy.', 'fields' => $fields];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Podaj poprawny adres e-mail.', 'fields' => $fields];
    }
    if (text_length($name) > 120 || text_length($email) > 180 || text_length($phone) > 40 || text_length($message) > 4000) {
        return ['ok' => false, 'message' => 'Skróć dane w formularzu i spróbuj ponownie.', 'fields' => $fields];
    }
    if (is_contact_rate_limited()) {
        return ['ok' => false, 'message' => 'Otrzymaliśmy zbyt wiele zgłoszeń z tego adresu. Spróbuj ponownie później.', 'fields' => $fields];
    }

    $record = [
        'created_at' => date('c'),
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message,
    ];

    if (!ensure_storage_dir()) {
        return ['ok' => false, 'message' => 'Nie udało się zapisać wiadomości. Skontaktuj się z kancelarią telefonicznie lub mailowo.'];
    }

    $line = json_encode($record, JSON_UNESCAPED_UNICODE);
    if ($line === false) {
        return ['ok' => false, 'message' => 'Nie udało się przygotować wiadomości do zapisu. Skontaktuj się z kancelarią telefonicznie lub mailowo.'];
    }

    $written = file_put_contents(storage_dir() . '/contact-submissions.jsonl', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    if ($written === false) {
        return ['ok' => false, 'message' => 'Nie udało się zapisać wiadomości. Skontaktuj się z kancelarią telefonicznie lub mailowo.'];
    }

    return ['ok' => true, 'message' => 'Dziękujemy. Wiadomość została zapisana po stronie serwera.'];
}
