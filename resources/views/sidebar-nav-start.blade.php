{{--
    Rendered at the top of the sidebar navigation: the spotlight trigger, and a control to
    expand or collapse every group at once. With sixty resources across fourteen groups,
    opening them one at a time to find something is the difference between a usable tree
    and an unusable one.
--}}
@include('filament-brigada-cms::sidebar-search')

<div x-data="brigadaNavGroupControls" x-show="$store.sidebar.isOpen" x-cloak class="fi-brigada-nav-toggle-groups">
    <button
        type="button"
        x-on:click="toggleAll()"
        x-bind:aria-expanded="allOpen"
        class="fi-brigada-nav-toggle-groups-btn"
    >
        <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::ChevronDown" />

        <span x-text="allOpen
            ? @js(__('filament-brigada-cms::cms.navigation.collapse_all'))
            : @js(__('filament-brigada-cms::cms.navigation.expand_all'))"></span>
    </button>
</div>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('brigadaNavGroupControls', () => ({
                get allOpen() {
                    return (this.$store.sidebar.collapsedGroups ?? []).length === 0
                },

                toggleAll() {
                    this.allOpen ? this.collapseAll() : this.expandAll()
                },

                expandAll() {
                    this.$store.sidebar.collapsedGroups = []
                },

                collapseAll() {
                    const labels = Array.from(
                        this.$el
                            .closest('.fi-sidebar-nav')
                            .querySelectorAll('.fi-sidebar-group.fi-collapsible[data-group-label]'),
                    )
                        .map((group) => group.dataset.groupLabel)
                        .filter(Boolean)

                    this.$store.sidebar.collapsedGroups = [
                        ...new Set([...this.$store.sidebar.collapsedGroups, ...labels]),
                    ]
                },
            }))
        })
    </script>
@endonce
