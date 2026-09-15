<?php

namespace Wotz\FilamentBrigadaCms\Support;

class Labels
{
    /**
     * Humanise a component name into a label, keeping acronyms upper-cased.
     *
     * Filament derives labels from component names, which lower-cases acronyms —
     * "Amount including vat", "Image id", "Seo". Add project-specific acronyms through
     * `filament-brigada-cms.acronyms` rather than editing this list.
     *
     * A name may be a state path rather than a bare attribute — `data.title`, or
     * `form.nl.title` inside translatable tabs — and it is the last segment that
     * names the thing. Filament's own default takes the same one.
     */
    public static function humanise(string $name): string
    {
        $label = (string) str($name)
            ->afterLast('.')
            ->kebab()
            ->replace(['-', '_'], ' ')
            ->ucfirst();

        $acronyms = self::acronyms();

        if ($acronyms === []) {
            return $label;
        }

        return (string) preg_replace_callback(
            '/\b(' . implode('|', array_map('preg_quote', $acronyms)) . ')\b/i',
            fn (array $matches): string => mb_strtoupper($matches[1]),
            $label,
        );
    }

    /**
     * @return array<int, string>
     */
    public static function acronyms(): array
    {
        return array_values(array_filter((array) config('filament-brigada-cms.acronyms', [])));
    }
}
