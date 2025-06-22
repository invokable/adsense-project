<x-mail::message>
# AdSense レポート（今月）

今月のAdSenseレポートをお送りします。

## 📈 重要指標

<x-mail::table>
| 期間 | 収益 |
|:-----|-----:|
| **本日** | **¥{{ number_format($keyMetrics['today'] ?? 0) }}** |
| **昨日** | **¥{{ number_format($keyMetrics['yesterday'] ?? 0) }}** |
| **今月** | **¥{{ number_format($keyMetrics['thisMonth'] ?? 0) }}** |
</x-mail::table>

## 合計実績

**収益:** ¥{{ number_format($totalMetrics['earnings']) }}  
**ページビュー:** {{ number_format($totalMetrics['pageViews']) }}  
**クリック数:** {{ number_format($totalMetrics['clicks']) }}  
**CPC:** ¥{{ number_format($totalMetrics['cpc']) }}

## 日平均実績

**収益:** ¥{{ number_format($averageMetrics['earnings']) }}  
**ページビュー:** {{ number_format($averageMetrics['pageViews']) }}  
**クリック数:** {{ number_format($averageMetrics['clicks']) }}  
**CPC:** ¥{{ number_format($averageMetrics['cpc']) }}

@if(isset($recentDays) && count($recentDays) > 0)
## 日別詳細（直近7日）

@foreach($recentDays as $day)
**📅 {{ $day['date'] }}**  
　収益: ¥{{ number_format($day['earnings']) }} | ページビュー: {{ number_format($day['pageViews']) }} | クリック数: {{ number_format($day['clicks']) }} | CPC: ¥{{ number_format($day['cpc']) }}

@endforeach
@endif

---

レポート作成日時: {{ $reportDate }}

@lang('Regards,')<br>
{{ config('app.name') }}
</x-mail::message>