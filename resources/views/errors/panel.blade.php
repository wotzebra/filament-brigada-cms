{{--
    Error page for a Filament panel. Without it an unknown `/admin/...` URL — a stale
    bookmark, a typo, a resource an administrator has no access to — renders the public
    site's branded error page, which is disorienting and leaks the front-end chrome into
    the CMS.

    Point your `resources/views/errors/admin.blade.php` at this, or publish and edit it.
--}}
@php
    $status = $exception?->getStatusCode() ?? 500;
    $key = in_array($status, [403, 404, 419, 503], true) ? $status : 'default';
@endphp

<x-filament-panels::layout.simple>
    {{-- Rendered by the exception handler, outside the panel, so the render hook that
         normally applies the palette does not fire here. --}}
    @include('filament-brigada-theme::palette-boot')

    <section class="fi-brigada-error">
        <p class="fi-brigada-error-status">{{ $status }}</p>

        <h1 class="fi-brigada-error-heading">{{ __("filament-brigada-cms::cms.errors.{$key}.heading") }}</h1>

        <p class="fi-brigada-error-description">{{ __("filament-brigada-cms::cms.errors.{$key}.description") }}</p>

        <x-filament::button
            tag="a"
            :href="\Filament\Facades\Filament::getUrl()"
            :icon="\Filament\Support\Icons\Heroicon::OutlinedArrowLeft"
        >
            {{ __('filament-brigada-cms::cms.errors.back') }}
        </x-filament::button>
    </section>
</x-filament-panels::layout.simple>
