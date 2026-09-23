<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Induction\InductionService;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$cascade = !empty($_POST['cascade']);

$result = (new InductionService())->delete($id, $cascade);

if ($result['success']) {
    flash('success', 'Induction deleted.');
} else {
    flash('error', $result['errors']['form'] ?? 'Unable to delete induction.');
}

redirect('/admin/inductions/index.php');
