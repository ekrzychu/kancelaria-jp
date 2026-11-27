<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

$port = filter_var(getenv('DB_PORT'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
$schema = getenv('DB_DATABASE') ?: '';
$refuse = static function (): never {
    fwrite(STDERR, "This command requires a disposable test database.\n");
    exit(1);
};
if (getenv('APP_ENV') !== 'testing' || getenv('DB_CONNECTION') !== 'mariadb' || getenv('DB_HOST') !== '127.0.0.1'
    || ! $port || $port === 3306 || getenv('DB_USERNAME') !== 'kjp_test' || ! getenv('DB_PASSWORD')
    || getenv('DB_URL') || getenv('DB_SOCKET') || ! preg_match('/\Akancelaria_jp_test_[a-f0-9]{16}\z/', $schema)) {
    $refuse();
}

require $argv[1].'/vendor/autoload.php';
$app = require $argv[1].'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$db = config('database.connections.mariadb');
if (! $app->environment('testing') || config('database.default') !== 'mariadb'
    || $db['driver'] !== 'mariadb' || $db['host'] !== '127.0.0.1' || (string) $db['port'] !== (string) $port
    || $db['username'] !== 'kjp_test' || $db['database'] !== $schema || $db['url'] || $db['unix_socket']) {
    $refuse();
}
