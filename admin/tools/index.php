<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;

Auth::requireRole('admin');

require __DIR__ . '/../../views/admin/tools/index.php';
