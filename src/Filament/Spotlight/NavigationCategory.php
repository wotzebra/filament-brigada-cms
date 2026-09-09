<?php

namespace Wotz\FilamentBrigadaCms\Filament\Spotlight;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Wezlo\FilamentSearchSpotlight\Categories\Category;
use Wezlo\FilamentSearchSpotlight\Categories\ResourcesCategory;
use Wezlo\FilamentSearchSpotlight\Data\SpotlightResult;

/**
 * Searches the panel's sidebar navigation, which is already filtered by access
 * permissions, unlike the plugin's default resources and pages categories.
 */
class NavigationCategory implements Category
{
    public function key(): string
    {
        return 'navigation';
    }

    public function label(): string
    {
        return 'Navigation';
    }

    public function search(string $query, int $limit): array
    {
        $needle = mb_strtolower(trim($query));

        $results = [];

        foreach (Filament::getNavigation() as $groupOrItem) {
            $isGroup = $groupOrItem instanceof NavigationGroup;

            $groupLabel = $isGroup ? $groupOrItem->getLabel() : null;
            $items = $isGroup ? $groupOrItem->getItems() : [$groupOrItem];

            foreach ($items as $item) {
                $result = $this->getItemResult($item, $groupLabel, $needle);

                if ($result === null) {
                    continue;
                }

                $results[] = $result;

                if (count($results) >= $limit) {
                    return $results;
                }
            }
        }

        return $results;
    }

    protected function getItemResult(NavigationItem $item, ?string $groupLabel, string $needle): ?SpotlightResult
    {
        $url = $item->getUrl();

        if (blank($url)) {
            return null;
        }

        if (! $this->matches($item->getLabel(), $groupLabel, $needle)) {
            return null;
        }

        return new SpotlightResult(
            id: 'navigation:' . md5("{$groupLabel}:{$item->getLabel()}"),
            category: $this->key(),
            title: $item->getLabel(),
            subtitle: $groupLabel,
            icon: ResourcesCategory::normalizeIcon($item->getIcon()),
            url: $url,
        );
    }

    protected function matches(string $itemLabel, ?string $groupLabel, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        if (str_contains(mb_strtolower($itemLabel), $needle)) {
            return true;
        }

        return filled($groupLabel) && str_contains(mb_strtolower($groupLabel), $needle);
    }
}
