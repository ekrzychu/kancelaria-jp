<?php

declare(strict_types=1);

// This bridge is available only to the standalone CLI regression harness.
$schema = $argv[2] ?? '';
$host = getenv('TEST_DB_HOST');
$port = filter_var(getenv('TEST_DB_PORT'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
$user = getenv('TEST_DB_USERNAME');
$password = getenv('TEST_DB_PASSWORD');

if ($host !== '127.0.0.1' || ! $port || $port === 3306 || $user !== 'kjp_test' || ! $password
    || ! preg_match('/\Akancelaria_jp_test_[a-f0-9]{16}\z/', $schema)) {
    fwrite(STDERR, "A dedicated loopback MariaDB test account, separate port and disposable schema are required.\n");
    exit(1);
}

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    if (! preg_match('/\A(?:11\.4|12\.3)\.\d+.*MariaDB/i', $version)) {
        throw new RuntimeException('The regression suite requires MariaDB 11.4 or 12.3.');
    }

    switch ($argv[1] ?? '') {
        case 'create':
            // An existing schema causes failure. Only the harness which creates a schema drops it.
            $pdo->exec("CREATE DATABASE `{$schema}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo $version;
            break;

        case 'drop':
            $pdo->exec("DROP DATABASE `{$schema}`");
            break;

        case 'query':
            $pdo->exec("USE `{$schema}`");
            $pdo->exec("SET time_zone = '+00:00'");
            $operations = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
            $results = [];
            foreach ($operations as $operation) {
                $statement = $pdo->prepare($operation['sql']);
                $statement->execute($operation['params'] ?? []);
                $results[] = $statement->columnCount() > 0 ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
            }
            echo json_encode($results, JSON_THROW_ON_ERROR);
            break;

        default:
            throw new RuntimeException('Unknown database test operation.');
    }
} catch (PDOException $exception) {
    fwrite(STDERR, 'Disposable MariaDB operation failed (SQLSTATE '.$exception->getCode().").\n");
    exit(1);
} catch (RuntimeException | JsonException $exception) {
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
    exit(1);
}
