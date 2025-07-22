<x-mail::message>
# AdSense レポート

最新のAdSenseレポートをお送りします。

## 📈 重要指標

<x-mail::panel>
<x-mail::table>
| **本日** | **昨日** | **今月** |
|:--------:|:--------:|:--------:|
| **¥{{ number_format($keyMetrics['today'] ?? 0) }}** | **¥{{ number_format($keyMetrics['yesterday'] ?? 0) }}**@if($yesterdayChange['showComparison'] ?? false)<br><span class="change-text">@if($yesterdayChange['direction'] === 'up')▲@elseif($yesterdayChange['direction'] === 'down')▼@endif{{ $yesterdayChange['amount'] >= 0 ? '+' : '' }}¥{{ number_format(abs($yesterdayChange['amount'])) }}({{ $yesterdayChange['amount'] >= 0 ? '+' : '' }}{{ number_format($yesterdayChange['percentage'], 1) }}%)</span>@endif | **¥{{ number_format($keyMetrics['thisMonth'] ?? 0) }}** |
</x-mail::table>
</x-mail::panel>

## 合計実績

**収益:** ¥{{ number_format($totalMetrics['earnings']) }}  
**ページビュー:** {{ number_format($totalMetrics['pageViews']) }}  
**広告表示回数:** {{ number_format($totalMetrics['adImpressions']) }}  
**ビューアビリティ:** {{ number_format($totalMetrics['viewability'], 1) }}%

## 日平均実績

**収益:** ¥{{ number_format($averageMetrics['earnings']) }}  
**ページビュー:** {{ number_format($averageMetrics['pageViews']) }}  
**広告表示回数:** {{ number_format($averageMetrics['adImpressions']) }}  
**ビューアビリティ:** {{ number_format($averageMetrics['viewability'], 1) }}%

@if(isset($domainBreakdown) && count($domainBreakdown) > 0)
## ドメイン別実績

@foreach($domainBreakdown as $domain => $metrics)
**🌐 {{ $domain }}**  
　収益: ¥{{ number_format($metrics['earnings']) }} | ページビュー: {{ number_format($metrics['pageViews']) }} | 広告表示: {{ number_format($metrics['adImpressions']) }} | ビューアビリティ: {{ number_format($metrics['viewability'], 1) }}%

@endforeach
@endif

@if(isset($recentDays) && count($recentDays) > 0)
## 日別詳細（直近7日）

@foreach($recentDays as $day)
**📅 {{ $day['date'] }} ({{ $day['domain'] }})**  
　収益: ¥{{ number_format($day['earnings']) }} | ページビュー: {{ number_format($day['pageViews']) }} | 広告表示: {{ number_format($day['adImpressions']) }} | ビューアビリティ: {{ number_format($day['viewability'], 1) }}%

@endforeach
@endif

---

レポート作成日時: {{ $reportDate }}

@lang('Regards,')<br>
{{ config('app.name') }}
</x-mail::message>
