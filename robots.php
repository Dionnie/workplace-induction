<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

if (is_search_engine_discouraged()) {
    echo "User-agent: *\nDisallow: /\n";
} else {
    echo "User-agent: *\nAllow: /\n";
}
