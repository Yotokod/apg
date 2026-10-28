{{-- One animated scene: a registered illustration (LessonPlayer::SCENES) or a Font Awesome icon fallback. --}}
@props(['illustration' => null, 'icon' => null, 'label' => null])
@php
    $component = \App\Livewire\LessonPlayer::SCENES[$illustration] ?? null;
    $icon = is_string($icon) && preg_match('/^fa-[a-z0-9-]+$/', $icon) ? $icon : 'fa-lightbulb';
@endphp
@if ($component)
    <x-dynamic-component :component="$component" :label="$label" {{ $attributes->class('h-full w-full') }} />
@else
    <div {{ $attributes->class('lsn-icon-scene relative grid h-full w-full place-items-center') }} role="img" aria-label="{{ $label }}">
        <span class="lsn-ring absolute aspect-square w-[46%] rounded-full bg-brand-100/80" aria-hidden="true"></span>
        <span class="lsn-ring absolute aspect-square w-[34%] rounded-full bg-brand-500/20" style="--d: .6s" aria-hidden="true"></span>
        <span class="lsn-a-pop relative grid aspect-square w-[24%] place-items-center rounded-full bg-brand-600 text-white shadow-lg">
            <i class="fa-solid {{ $icon }} text-[clamp(1.5rem,6vw,3.5rem)]" aria-hidden="true"></i>
        </span>
    </div>
@endif
