<?php

namespace Wotz\FilamentBrigadaCms\Concerns;

/**
 * Marks a resource as read-only in the sidebar: records come from an external system and
 * cannot be edited here. Use on resources synced from an ERP, a PIM, or similar.
 */
trait HasReadOnlyBadge
{
    public static function getNavigationBadge(): ?string
    {
        return __('filament-brigada-cms::cms.read_only.badge');
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('filament-brigada-cms::cms.read_only.tooltip');
    }
}
