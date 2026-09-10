<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures;

use Wotz\FilamentBrigadaCms\Providers\BrigadaPanelProvider;

/**
 * What a project's own `AdminPanelProvider` looks like: an id and nothing else.
 *
 * The seams are protected, so this exposes them the way a project overriding one would
 * reach them.
 */
class ProjectPanelProvider extends BrigadaPanelProvider
{
    protected function id(): string
    {
        return 'project';
    }

    /** @return array<string, mixed> */
    public function pluginSet(): array
    {
        return $this->plugins();
    }

    /** @return array<int, class-string> */
    public function middlewareStack(): array
    {
        return $this->middleware();
    }

    /** @return array<int, class-string> */
    public function spotlightCategorySet(): array
    {
        return $this->spotlightCategories();
    }
}
