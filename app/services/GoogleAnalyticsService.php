<?php
/**
 * Google Analytics 4 Data API – dashboard istatistikleri
 * Analytics::getDashboardStats() ile aynı anahtarlarda dizi döndürür.
 */

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;

class GoogleAnalyticsService
{
    /** @var string */
    private $propertyId;

    /** @var BetaAnalyticsDataClient */
    private $client;

    /**
     * @param string $propertyId GA4 Property ID (sayısal, örn. 123456789)
     * @param string $credentialsJson Servis hesabı JSON içeriği
     * @throws Exception
     */
    public function __construct($propertyId, $credentialsJson)
    {
        $this->propertyId = trim((string) $propertyId);
        if ($this->propertyId === '') {
            throw new Exception('GA4 Property ID boş olamaz.');
        }
        $credentials = json_decode(trim($credentialsJson), true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($credentials['type'])) {
            throw new Exception('Geçersiz servis hesabı JSON.');
        }
        $this->client = new BetaAnalyticsDataClient([
            'credentials' => $credentials,
        ]);
    }

    /**
     * Dashboard için özet istatistikler (Analytics::getDashboardStats() ile aynı format).
     *
     * @return array{today_views: int, today_unique: int, month_views: int, month_unique: int, live_visitors: int, avg_duration: int, top_pages: array, device_distribution: array}
     * @throws Exception
     */
    public function getDashboardStats()
    {
        $propertyName = 'properties/' . $this->propertyId;
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-d');
        $days30Ago = date('Y-m-d', strtotime('-30 days'));
        $days7Ago = date('Y-m-d', strtotime('-7 days'));

        $todayViews = $this->fetchMetricTotal($propertyName, $today, $today, 'screenPageViews');
        $todayUnique = $this->fetchMetricTotal($propertyName, $today, $today, 'activeUsers');
        $monthViews = $this->fetchMetricTotal($propertyName, $monthStart, $monthEnd, 'screenPageViews');
        $monthUnique = $this->fetchMetricTotal($propertyName, $monthStart, $monthEnd, 'activeUsers');
        $avgDurationSeconds = (int) $this->fetchMetricTotal($propertyName, $days30Ago, $today, 'averageSessionDuration');
        $topPages = $this->fetchTopPages($propertyName, $days7Ago, $today, 10);
        $deviceDistribution = $this->fetchDeviceDistribution($propertyName, $days30Ago, $today);

        return [
            'today_views' => (int) $todayViews,
            'today_unique' => (int) $todayUnique,
            'month_views' => (int) $monthViews,
            'month_unique' => (int) $monthUnique,
            'live_visitors' => 0,
            'avg_duration' => $avgDurationSeconds,
            'top_pages' => $topPages,
            'device_distribution' => $deviceDistribution,
        ];
    }

    /**
     * Tek metrik, tarih aralığı – toplam değer.
     */
    private function fetchMetricTotal($propertyName, $startDate, $endDate, $metricName)
    {
        $request = (new RunReportRequest())
            ->setProperty($propertyName)
            ->setDateRanges([
                (new DateRange())->setStartDate($startDate)->setEndDate($endDate),
            ])
            ->setMetrics([new Metric(['name' => $metricName])]);

        $response = $this->client->runReport($request);
        $rows = $response->getRows();
        if (count($rows) > 0) {
            $mv = $rows[0]->getMetricValues();
            return $mv->count() > 0 ? $mv[0]->getValue() : 0;
        }
        $totals = $response->getTotals();
        if (count($totals) > 0) {
            $mv = $totals[0]->getMetricValues();
            return $mv->count() > 0 ? $mv[0]->getValue() : 0;
        }
        return 0;
    }

    /**
     * En çok görüntülenen sayfalar (pagePath, screenPageViews).
     *
     * @return array<int, array{page_url: string, page_title: string, views: int}>
     */
    private function fetchTopPages($propertyName, $startDate, $endDate, $limit)
    {
        $request = (new RunReportRequest())
            ->setProperty($propertyName)
            ->setDimensions([new Dimension(['name' => 'pagePath'])])
            ->setMetrics([new Metric(['name' => 'screenPageViews'])])
            ->setDateRanges([
                (new DateRange())->setStartDate($startDate)->setEndDate($endDate),
            ])
            ->setOrderBys([
                (new OrderBy())
                    ->setMetric((new MetricOrderBy())->setMetricName('screenPageViews'))
                    ->setDesc(true),
            ])
            ->setLimit($limit);

        $response = $this->client->runReport($request);
        $result = [];
        foreach ($response->getRows() as $row) {
            $dimVals = $row->getDimensionValues();
            $metricVals = $row->getMetricValues();
            $path = $dimVals->count() > 0 ? $dimVals[0]->getValue() : '';
            $views = $metricVals->count() > 0 ? (int) $metricVals[0]->getValue() : 0;
            $result[] = [
                'page_url' => $path,
                'page_title' => $path === '/' || $path === '' ? 'Ana Sayfa' : trim($path, '/'),
                'views' => $views,
            ];
        }
        return $result;
    }

    /**
     * Cihaz dağılımı (deviceCategory -> count).
     *
     * @return array<int, array{device_type: string, count: int}>
     */
    private function fetchDeviceDistribution($propertyName, $startDate, $endDate)
    {
        $request = (new RunReportRequest())
            ->setProperty($propertyName)
            ->setDimensions([new Dimension(['name' => 'deviceCategory'])])
            ->setMetrics([new Metric(['name' => 'activeUsers'])])
            ->setDateRanges([
                (new DateRange())->setStartDate($startDate)->setEndDate($endDate),
            ]);

        $response = $this->client->runReport($request);
        $result = [];
        $map = ['desktop' => 'desktop', 'mobile' => 'mobile', 'tablet' => 'tablet'];
        foreach ($response->getRows() as $row) {
            $dimVals = $row->getDimensionValues();
            $metricVals = $row->getMetricValues();
            $device = $dimVals->count() > 0 ? strtolower($dimVals[0]->getValue()) : 'desktop';
            if (!isset($map[$device])) {
                $map[$device] = $device;
            }
            $count = $metricVals->count() > 0 ? (int) $metricVals[0]->getValue() : 0;
            $result[] = [
                'device_type' => $map[$device],
                'count' => $count,
            ];
        }
        return $result;
    }
}
