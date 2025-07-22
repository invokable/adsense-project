<x-mail::message>
# AdSense Report

Here's your latest AdSense report.

## 📈 Key Metrics

<x-mail::panel>
<x-mail::table>
| **Today** | **Yesterday** | **This Month** |
|:---------:|:-------------:|:--------------:|
| **${{ number_format($keyMetrics['today'] ?? 0, 2) }}** | **${{ number_format($keyMetrics['yesterday'] ?? 0, 2) }}**@if($yesterdayChange['showComparison'] ?? false)<br><span class="change-text">@if($yesterdayChange['direction'] === 'up')▲@elseif($yesterdayChange['direction'] === 'down')▼@endif{{ $yesterdayChange['amount'] >= 0 ? '+' : '' }}${{ number_format(abs($yesterdayChange['amount']), 2) }}({{ $yesterdayChange['amount'] >= 0 ? '+' : '' }}{{ number_format($yesterdayChange['percentage'], 1) }}%)</span>@endif | **${{ number_format($keyMetrics['thisMonth'] ?? 0, 2) }}** |
</x-mail::table>
</x-mail::panel>

## Total Performance

**Earnings:** ${{ number_format($totalMetrics['earnings'], 2) }}  
**Page Views:** {{ number_format($totalMetrics['pageViews']) }}  
**Ad Impressions:** {{ number_format($totalMetrics['adImpressions']) }}  
**Viewability:** {{ number_format($totalMetrics['viewability'], 1) }}%

## Daily Average Performance

**Earnings:** ${{ number_format($averageMetrics['earnings'], 2) }}  
**Page Views:** {{ number_format($averageMetrics['pageViews']) }}  
**Ad Impressions:** {{ number_format($averageMetrics['adImpressions']) }}  
**Viewability:** {{ number_format($averageMetrics['viewability'], 1) }}%

@if(isset($domainBreakdown) && count($domainBreakdown) > 0)
## Domain Breakdown

@foreach($domainBreakdown as $domain => $metrics)
**🌐 {{ $domain }}**  
 Earnings: ${{ number_format($metrics['earnings'], 2) }} | Page Views: {{ number_format($metrics['pageViews']) }} | Ad Impressions: {{ number_format($metrics['adImpressions']) }} | Viewability: {{ number_format($metrics['viewability'], 1) }}%

@endforeach
@endif

@if(isset($recentDays) && count($recentDays) > 0)
## Daily Details (Recent 7 Days)

@foreach($recentDays as $day)
**📅 {{ $day['date'] }} ({{ $day['domain'] }})**  
 Earnings: ${{ number_format($day['earnings'], 2) }} | Page Views: {{ number_format($day['pageViews']) }} | Ad Impressions: {{ number_format($day['adImpressions']) }} | Viewability: {{ number_format($day['viewability'], 1) }}%

@endforeach
@endif

---

Report Generated: {{ $reportDate }}

@lang('Regards,')<br>
{{ config('app.name') }}
</x-mail::message>
