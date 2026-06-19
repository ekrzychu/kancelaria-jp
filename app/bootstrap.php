<?php
declare(strict_types=1);

const APP_ROOT = __DIR__;
const PROJECT_ROOT = __DIR__ . '/..';

function data_file(string $name): array
{
    $path = APP_ROOT . '/data/' . $name . '.json';
    $json = is_file($path) ? file_get_contents($path) : '[]';
    $data = json_decode($json ?: '[]', true);

    return is_array($data) ? $data : [];
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
    return rtrim($path, '/') . '/';
}

function page_title(string $title, array $site): string
{
    return $title . ' — ' . ($site['name'] ?? 'Kancelaria');
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

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }

        $out .= mb_strtoupper(mb_substr($part, 0, 1));

        if (mb_strlen($out) >= 2) {
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
    return is_dir($dir) || mkdir($dir, 0755, true);
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
    $data = [];

    if (is_file($path)) {
        $json = file_get_contents($path);
        $decoded = json_decode($json ?: '{}', true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }

    foreach ($data as $rateKey => $timestamps) {
        if (!is_array($timestamps)) {
            unset($data[$rateKey]);
            continue;
        }
        $data[$rateKey] = array_values(array_filter(
            $timestamps,
            fn ($timestamp) => is_int($timestamp) && $timestamp >= $now - $windowSeconds
        ));
    }

    $attempts = $data[$key] ?? [];
    if (count($attempts) >= $limit) {
        file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL, LOCK_EX);

        return true;
    }

    $attempts[] = $now;
    $data[$key] = $attempts;
    file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL, LOCK_EX);

    return false;
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

    if ($website !== '') {
        return ['ok' => true, 'message' => 'Dziękujemy. Wiadomość została zapisana po stronie serwera.'];
    }

    if ($name === '' || $email === '' || $message === '') {
        return ['ok' => false, 'message' => 'Uzupełnij imię i nazwisko, adres e-mail oraz krótki opis sprawy.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Podaj poprawny adres e-mail.'];
    }
    if (mb_strlen($name) > 120 || mb_strlen($email) > 180 || mb_strlen($phone) > 40 || mb_strlen($message) > 4000) {
        return ['ok' => false, 'message' => 'Skróć dane w formularzu i spróbuj ponownie.'];
    }
    if (is_contact_rate_limited()) {
        return ['ok' => false, 'message' => 'Otrzymaliśmy zbyt wiele zgłoszeń z tego adresu. Spróbuj ponownie później.'];
    }

    $record = [
        'created_at' => date('c'),
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
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
