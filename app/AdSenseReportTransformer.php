<?php

namespace App;

class AdSenseReportTransformer
{
    /**
     * Transform raw AdSense report data into notification-ready data structure
     */
    public function toNotificationData(array $rawReports): array
    {
        $totalMetrics = [
            'earnings' => $this->getMetricValue('ESTIMATED_EARNINGS', $rawReports),
            'pageViews' => $this->getMetricValue('PAGE_VIEWS', $rawReports),
            'clicks' => $this->getMetricValue('CLICKS', $rawReports),
            'cpc' => $this->getMetricValue('COST_PER_CLICK', $rawReports),
        ];

        $averageMetrics = [
            'earnings' => $this->getMetricValue('ESTIMATED_EARNINGS', $rawReports, 'averages'),
            'pageViews' => $this->getMetricValue('PAGE_VIEWS', $rawReports, 'averages'),
            'clicks' => $this->getMetricValue('CLICKS', $rawReports, 'averages'),
            'cpc' => $this->getMetricValue('COST_PER_CLICK', $rawReports, 'averages'),
        ];

        // Get key daily metrics
        $rows = $rawReports['rows'] ?? [];
        $todayEarnings = $this->findEarningsByDate($rows, now()->format('Y-m-d'));
        $yesterdayEarnings = $this->findEarningsByDate($rows, now()->subDay()->format('Y-m-d'));
        $yesterdayWeekAgoEarnings = $this->findEarningsByDate($rows, now()->subDays(8)->format('Y-m-d'));

        $keyMetrics = [
            'today' => $todayEarnings,
            'yesterday' => $yesterdayEarnings,
            'thisMonth' => $totalMetrics['earnings'],
        ];

        // Calculate yesterday's change compared to a week ago (only if both data are available)
        $yesterdayChange = $this->calculateEarningsChange($yesterdayEarnings, $yesterdayWeekAgoEarnings);

        $recentDays = [];
        if (isset($rawReports['rows']) && count($rawReports['rows']) > 0) {
            $recentRows = array_slice($rawReports['rows'], 0, 7);
            foreach ($recentRows as $row) {
                $recentDays[] = [
                    'date' => $row['cells'][0]['value'] ?? 'N/A',
                    'earnings' => $this->getMetricValueFromRow('ESTIMATED_EARNINGS', $row),
                    'pageViews' => $this->getMetricValueFromRow('PAGE_VIEWS', $row),
                    'clicks' => $this->getMetricValueFromRow('CLICKS', $row),
                    'cpc' => $this->getMetricValueFromRow('COST_PER_CLICK', $row),
                ];
            }
        }

        return [
            'keyMetrics' => $keyMetrics,
            'yesterdayChange' => $yesterdayChange,
            'totalMetrics' => $totalMetrics,
            'averageMetrics' => $averageMetrics,
            'recentDays' => $recentDays,
            'reportDate' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get metric value by name from data source
     */
    private function getMetricValue(string $metricName, array $rawReports, string $section = 'totals'): float
    {
        $metrics = config('ads.metrics');
        $index = array_search($metricName, $metrics);

        if ($index === false) {
            return 0;
        }

        $dataSource = $rawReports[$section] ?? [];
        $value = $dataSource['cells'][$index + 1]['value'] ?? 0;

        return (float) $value;
    }

    /**
     * Get metric value from a specific row
     */
    private function getMetricValueFromRow(string $metricName, array $row): float
    {
        $metrics = config('ads.metrics');
        $index = array_search($metricName, $metrics);

        if ($index === false) {
            return 0;
        }

        $value = $row['cells'][$index + 1]['value'] ?? 0;

        return (float) $value;
    }

    /**
     * Find earnings by date from rows data
     */
    private function findEarningsByDate(array $rows, string $targetDate): float
    {
        foreach ($rows as $row) {
            $date = $row['cells'][0]['value'] ?? '';
            if ($date === $targetDate) {
                return $this->getMetricValueFromRow('ESTIMATED_EARNINGS', $row);
            }
        }

        return 0.0;
    }

    /**
     * Calculate change in earnings compared to a week ago
     */
    private function calculateEarningsChange(float $currentEarnings, float $previousEarnings): array
    {
        // If no data for comparison week (e.g., early in the month), return null to hide comparison
        if ($previousEarnings == 0 && $currentEarnings > 0) {
            return [
                'showComparison' => false,
                'amount' => 0,
                'percentage' => 0,
                'direction' => 'neutral',
            ];
        }

        // If both are 0, still don't show comparison
        if ($previousEarnings == 0) {
            return [
                'showComparison' => false,
                'amount' => $currentEarnings,
                'percentage' => 0,
                'direction' => $currentEarnings > 0 ? 'up' : 'neutral',
            ];
        }

        $change = $currentEarnings - $previousEarnings;
        $percentage = ($change / $previousEarnings) * 100;

        return [
            'showComparison' => true,
            'amount' => $change,
            'percentage' => $percentage,
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }
}
