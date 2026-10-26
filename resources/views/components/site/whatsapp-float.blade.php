@php($wa = \App\Support\SiteData::whatsappUrl(__('wa.msg')))
@if ($wa)
    <a href="{{ $wa }}" target="_blank" rel="noopener" aria-label="{{ __('wa.float') }}" title="{{ __('wa.float') }}"
       class="fixed bottom-4 left-4 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#1a9e4b] text-3xl text-white shadow-lg transition hover:bg-[#157f3c]">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
    </a>
@endif
