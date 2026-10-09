{{--
    filament-socialite's login buttons, with the Brigada logo on the Zenith button.
    Kept in step with dutchcodingcompany/filament-socialite's own
    resources/views/components/buttons.blade.php.
--}}
<div
    x-data="{}"
    x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-socialite-styles', package: 'filament-socialite'))]"
>
    <div class="flex flex-col gap-y-6">
        @if ($messageBag->isNotEmpty())
            @foreach($messageBag->all() as $value)
                <p class="fi-fo-field-wrp-error-message text-danger-600 dark:text-danger-400">{{ __($value) }}</p>
            @endforeach
        @endif

        @if (count($visibleProviders))
            @if($showDivider)
                <div class="relative flex items-center justify-center text-center">
                    <div class="absolute border-t border-gray-200 w-full h-px"></div>
                    <p class="inline-block relative bg-white text-sm p-2 rounded-full font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-100">
                        {{ __('filament-socialite::auth.login-via') }}
                    </p>
                </div>
            @endif

            <div class="grid @if(count($visibleProviders) > 1) grid-cols-2 @endif gap-4">
                @foreach($visibleProviders as $key => $provider)
                    <x-filament::button
                        :color="$provider->getColor()"
                        :outlined="$provider->getOutlined()"
                        :icon="$provider->getIcon()"
                        tag="a"
                        :href="route($socialiteRoute, $key)"
                        :spa-mode="false"
                    >
                        @if ($key === \Wotz\FilamentBrigadaCms\Filament\Socialite\ZenithLogin::PROVIDER)
                            <span class="inline-flex items-center gap-1.5" aria-hidden="true">
                                {{ __('filament-brigada-cms::cms.zenith.login_with') }}
                                @svg('brigada-logo', 'h-4! w-auto!')
                            </span>
                            <span class="sr-only">{{ $provider->getLabel() }}</span>
                        @else
                            {{ $provider->getLabel() }}
                        @endif
                    </x-filament::button>
                @endforeach
            </div>
        @else
            <span></span>
        @endif
    </div>
</div>
