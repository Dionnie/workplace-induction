<?php

declare(strict_types=1);

use App\Notification\NotificationService;

/**
 * Secondary side effect of a successful induction completion. Notification
 * failures must never break the primary compliance workflow, so they are
 * caught and logged rather than allowed to propagate.
 */
add_action('induction_completed', function (array $inductee, array $induction, array $complianceRecord): void {
    $notifications = new NotificationService();

    try {
        $notifications->notifyInductionCompleted($inductee, $induction, $complianceRecord);
    } catch (\Throwable $e) {
        error_log('induction_completed admin notification failed: ' . $e->getMessage());
    }

    try {
        $notifications->notifyInducteeOfCompletion($inductee, $induction, $complianceRecord);
    } catch (\Throwable $e) {
        error_log('induction_completed inductee notification failed: ' . $e->getMessage());
    }
});
