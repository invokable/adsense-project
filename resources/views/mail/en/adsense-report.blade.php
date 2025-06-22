<x-mail::message>
# AdSense Report (This Month)

Here's your monthly AdSense report.

## 📈 Key Metrics

<x-mail::panel>
<x-mail::table>
| **Today** | **Yesterday** | **This Month** |
|:---------:|:-------------:|:--------------:|
| **${{ number_format($keyMetrics['today'] ?? 0, 2) }}** | **${{ number_format($keyMetrics['yesterday'] ?? 0, 2) }}**<br><span class="change-text">@if($yesterdayChange['direction'] === 'up')▲@elseif($yesterdayChange['direction'] === 'down')▼@endif{{ $yesterdayChange['amount'] >= 0 ? '+' : '' }}${{ number_format(abs($yesterdayChange['amount']), 2) }}({{ $yesterdayChange['amount'] >= 0 ? '+' : '' }}{{ number_format($yesterdayChange['percentage'], 1) }}%)</span> | **${{ number_format($keyMetrics['thisMonth'] ?? 0, 2) }}** |
</x-mail::table>
</x-mail::panel>

## Total Performance

**Earnings:** ${{ number_format($totalMetrics['earnings'], 2) }}  
**Page Views:** {{ number_format($totalMetrics['pageViews']) }}  
**Clicks:** {{ number_format($totalMetrics['clicks']) }}  
**CPC:** ${{ number_format($totalMetrics['cpc'], 2) }}

## Daily Average Performance

**Earnings:** ${{ number_format($averageMetrics['earnings'], 2) }}  
**Page Views:** {{ number_format($averageMetrics['pageViews']) }}  
**Clicks:** {{ number_format($averageMetrics['clicks']) }}  
**CPC:** ${{ number_format($averageMetrics['cpc'], 2) }}

@if(isset($recentDays) && count($recentDays) > 0)
## Daily Details (Recent 7 Days)

@foreach($recentDays as $day)
**📅 {{ $day['date'] }}**  
　Earnings: ${{ number_format($day['earnings'], 2) }} | Page Views: {{ number_format($day['pageViews']) }} | Clicks: {{ number_format($day['clicks']) }} | CPC: ${{ number_format($day['cpc'], 2) }}

@endforeach
@endif

---

Report Generated: {{ $reportDate }}

@lang('Regards,')<br>
{{ config('app.name') }}
</x-mail::message>