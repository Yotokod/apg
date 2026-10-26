@props(['value', 'total', 'label' => ''])
@php($pct = $total > 0 ? (int) round($value / $total * 100) : 0)
<div {{ $attributes }}>
    <div class="mb-1 flex justify-between text-xs font-semibold"><span>{{ $label }}</span><span>{{ $value }}/{{ $total }} · {{ $pct }}%</span></div>
    <div class="h-2 overflow-hidden rounded-full bg-stone-200 dark:bg-stone-800" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}" aria-label="{{ $label }}">
        <div class="h-full rounded-full bg-brand-600 transition-all" style="width: {{ $pct }}%"></div>
    </div>
</div>
