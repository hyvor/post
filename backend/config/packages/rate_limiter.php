<?php

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {

    // public API
    $framework->rateLimiter()
        ->limiter('public_api')
        ->policy('sliding_window')
        ->limit(30)
        ->interval('1 minute');
};
