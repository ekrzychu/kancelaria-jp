<?php

return [
    'enabled' => env('ANALYTICS_ENABLED', false) === true,
    'retention_days' => max(1, min(730, (int) env('ANALYTICS_RETENTION_DAYS', 365))),
];
