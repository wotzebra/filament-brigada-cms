<?php

namespace Wotz\FilamentBrigadaCms\Filament\Spotlight;

use Filament\Facades\Filament;
use Wezlo\FilamentSearchSpotlight\Categories\ActionsCategory;
use Wezlo\FilamentSearchSpotlight\Data\SpotlightResult;

/**
 * The plugin's auto-generated "Create {resource}" actions do not check
 * permissions, so filter them against the resource's canCreate().
 */
class AccessAwareActionsCategory extends ActionsCategory
{
    public function search(string $query, int $limit): array
    {
        $createActionResources = [];

        foreach (Filament::getCurrentPanel()?->getResources() ?? [] as $resourceClass) {
            $createActionResources['create-' . md5($resourceClass)] = $resourceClass;
        }

        $results = array_filter(
            parent::search($query, $limit),
            function (SpotlightResult $result) use ($createActionResources): bool {
                $resourceClass = $createActionResources[$result->payload['action'] ?? null] ?? null;

                if ($resourceClass === null) {
                    return true;
                }

                return $resourceClass::canCreate();
            },
        );

        return array_values($results);
    }
}
