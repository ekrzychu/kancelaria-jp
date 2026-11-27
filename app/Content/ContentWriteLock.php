<?php

namespace App\Content;

use Closure;
use Illuminate\Database\Connection;

final class ContentWriteLock
{
    public static function transaction(Connection $connection, Closure $operation, Closure $whenBusy): mixed
    {
        $lock = 'kjp-content-'.substr(hash('sha256', $connection->getDatabaseName()), 0, 40);
        $result = $connection->selectOne('SELECT GET_LOCK(?, 5) AS acquired', [$lock], false);
        if ((int) $result->acquired !== 1) {
            throw $whenBusy();
        }

        try {
            return $connection->transaction($operation);
        } finally {
            $connection->selectOne('SELECT RELEASE_LOCK(?)', [$lock], false);
        }
    }
}
