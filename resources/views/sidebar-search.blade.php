{{--
    Panel-wide search. Filament's own global search is disabled in favour of the spotlight
    overlay, which covers navigation and actions as well as records; this is its trigger.

    A real <button>, not an input styled to look like one: a readonly text field is
    announced as "search, edit text, read only", cannot be activated with Space, and gives
    no hint that it opens a dialog.
--}}
<div
    x-data="{ isMac: navigator.userAgentData?.platform === 'macOS' || /Mac|iPhone|iPad/.test(navigator.platform) }"
    @if (filament()->isSidebarCollapsibleOnDesktop() || filament()->isSidebarFullyCollapsibleOnDesktop())
        x-show="$store.sidebar.isOpen"
        x-cloak
    @endif
    class="fi-brigada-search"
>
    <button
        type="button"
        x-on:click="$dispatch('open-spotlight')"
        x-bind:aria-keyshortcuts="isMac ? 'Meta+K' : 'Control+K'"
        aria-haspopup="dialog"
        class="fi-brigada-search-btn"
    >
        <x-filament::icon
            :icon="\Filament\Support\Icons\Heroicon::MagnifyingGlass"
            class="fi-brigada-search-btn-icon"
        />

        <span class="fi-brigada-search-btn-label">
            {{ __('filament-brigada-cms::cms.search.placeholder') }}
        </span>

        <kbd aria-hidden="true" x-text="isMac ? '⌘K' : 'Ctrl K'" class="fi-brigada-search-btn-kbd"></kbd>
    </button>
</div>
