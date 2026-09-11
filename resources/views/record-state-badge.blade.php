{{--
    Whether the record open in front of you is the one the site is serving.

    Beside the title rather than in a button, because it describes the record and a
    button describes what pressing it does. Encoded in a button label — "Save to new
    draft" — it forces an editor to infer the state from the thing they are about to
    press, which is reasoning backwards.
--}}
<x-filament::badge
    :color="$published ? 'success' : 'warning'"
    class="fi-brigada-record-state"
    style="display: inline-flex; vertical-align: middle; margin-inline-start: 0.625rem"
>
    {{ $published
        ? __('filament-brigada-cms::cms.drafts.state.published')
        : __('filament-brigada-cms::cms.drafts.state.draft') }}
</x-filament::badge>
