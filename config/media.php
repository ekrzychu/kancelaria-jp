<?php

return [
    'root' => env('MEDIA_STORAGE_ROOT', storage_path('app/media')),
    'max_kilobytes' => 10 * 1024,
    'max_pixels' => 24_000_000,
    'max_edge' => 12000,
    'widths' => ['article' => 1920, 'portrait' => 1200, 'avatar' => 320],
    'quality' => 85,
    'temporary_hours' => 24,
];
