<?php

declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\DashboardRepository;
use App\Compliance\ComplianceRepository;
use App\Induction\InductionRepository;

class DashboardService
{
    private const EXPIRING_SOON_DAYS = 30;
    private const EXPIRING_SOON_LIMIT = 10;

    private DashboardRepository $dashboard;

    public function __construct()
    {
        $this->dashboard = new DashboardRepository();
    }

    /**
     * @return array<string, mixed>
     */
    public function metrics(): array
    {
        (new ComplianceRepository())->expireLapsed();

        return [
            'total_inductees' => $this->dashboard->countActiveInductees(),
            'active_inductions' => $this->dashboard->countActiveInductions(),
            'expiring_soon' => $this->dashboard->countExpiringSoon(self::EXPIRING_SOON_DAYS),
            'expired' => $this->dashboard->countComplianceByStatus('expired'),
            'expiring_soon_days' => self::EXPIRING_SOON_DAYS,
            'expiring_soon_list' => $this->dashboard->expiringSoon(self::EXPIRING_SOON_DAYS, self::EXPIRING_SOON_LIMIT),
            'induction_breakdown' => $this->inductionBreakdown(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function inductionBreakdown(): array
    {
        $inductions = (new InductionRepository())->all('active');
        $counts = $this->dashboard->perInductionComplianceCounts();

        $rows = [];
        foreach ($inductions as $induction) {
            $inductionCounts = $counts[(int) $induction['id']] ?? ['active' => 0, 'expired' => 0];
            $total = $inductionCounts['active'] + $inductionCounts['expired'];

            $rows[] = [
                'title' => $induction['title'],
                'code' => $induction['code'],
                'compliant' => $inductionCounts['active'],
                'expired' => $inductionCounts['expired'],
                'rate' => $total > 0 ? round(($inductionCounts['active'] / $total) * 100) : null,
            ];
        }

        return $rows;
    }
}
