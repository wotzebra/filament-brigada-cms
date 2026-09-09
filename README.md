# Filament Brigada CMS

The conventions, defaults and shared UI behind a Brigada Filament CMS — the parts that were
previously copied out of a project skeleton and then drifted.

Pulls in [`wotz/filament-brigada-theme`](https://github.com/wotzebra/filament-brigada-theme)
and the standard plugin set, so a project gets a styled, configured panel from one
`composer require`.

## Installation

```bash
composer require wotz/filament-brigada-cms
```

Import the stylesheets from your panel theme, with the other `@import` rules at the top —
CSS drops an `@import` that follows any other rule:

```css
@import '../../../../vendor/wotz/filament-brigada-theme/resources/css/filament-brigada-theme.css';
@import '../../../../vendor/wotz/filament-brigada-cms/resources/css/filament-brigada-cms.css';
```

Extend the panel provider:

```php
use Wotz\FilamentBrigadaCms\Providers\BrigadaPanelProvider;

class AdminPanelProvider extends BrigadaPanelProvider
{
    protected function id(): string
    {
        return 'admin';
    }
}
```

Then `npm run build`.

## What you get

**Panel** — the standard plugin set (theme, shield, activity log, sticky header, menu,
settings, media library, SEO, redirects, admin bar, environment indicator, unsaved-changes
modal, spotlight), Filament's global search replaced by the `⌘K` spotlight over navigation,
records and actions, and the middleware stack.

**Defaults**, all applied through `configureUsing`, so an explicit call in a resource
always wins:

| | |
|---|---|
| Non-clickable rows | cell text stays selectable; records open through row actions |
| Persisted table state | sort, search and column searches, not just filters |
| Humanised labels | `Amount including vat` → `Amount including VAT` |
| Collapsed nav groups | and a default group label |
| Date and money formats | `d M Y H:i:s`, `EUR` |

**Shared UI** — the `⌘K` trigger, expand/collapse-all for the sidebar, a panel error page,
a loading skeleton, and the `HasReadOnlyBadge` concern for resources synced from elsewhere.

**Drafts and live preview** — `oddvalue/laravel-drafts` and `wotz/filament-live-preview`
are wired up: the plugins are registered, draft previews are disabled inside the panel, and
two concerns add the actions to a resource's pages.

```php
use Wotz\FilamentBrigadaCms\Filament\Concerns\HandlesDraftsOnEdit;

class EditPage extends EditRecord
{
    use HandlesDraftsOnEdit;   // Publish, Save to new draft, Version switcher
}
```

### Opting a resource in

The package registers the plugins, but nothing appears until a resource opts in — a panel
with the plugins loaded and no traits applied shows no Preview or draft actions at all.

**On the model:**

```php
use Oddvalue\LaravelDrafts\Concerns\HasDrafts;
use Wotz\FilamentBrigadaCms\Models\Concerns\HandlesTranslatableDrafts;

class Page extends Model
{
    use HandlesTranslatableDrafts {
        HandlesTranslatableDrafts::getDraftableAttributes insteadof HasDrafts;
    }
    use HasDrafts;
}
```

The `insteadof` is required, not stylistic: both traits define `getDraftableAttributes()`,
and PHP fatals on the collision without it.

`HandlesTranslatableDrafts` matters more than it looks: `laravel-drafts` copies raw
attributes, so a `spatie/laravel-translatable` model loses every locale but the current one
the first time a draft is saved. On a model that is not translatable, use `HasDrafts` alone.

Add the columns with the migration helper the drafts package ships:

```php
Schema::table('pages', fn (Blueprint $table) => $table->drafts());
```

**On the pages:**

```php
class EditPage extends EditRecord
{
    use HandlesDraftsOnEdit;
    use HasLivePreviewComponent;   // from wotz/filament-live-preview

    protected function getHeaderActions(): array
    {
        return [
            $this->getLivePreviewAction(),
            $this->getSwitchVersionAction(),
            $this->getSaveFormAction()->submit(null)->action('save'),
            $this->getSaveDraftAction(),
            $this->getPublishAction(),
        ];
    }

    protected function getPreviewModalView(): ?string
    {
        return 'page.show';
    }

    protected function getPreviewModalDataRecordKey(): ?string
    {
        return 'page';
    }
}
```

Live preview renders your own front-end view, so it only applies to resources that have
one. If a project has no use for either, drop them:

```php
protected function plugins(): array
{
    $plugins = parent::plugins();
    unset($plugins['live-preview'], $plugins['peek']);

    return $plugins;
}
```

## Overriding

Every method on `BrigadaPanelProvider` is a seam. Append rather than replace:

```php
protected function plugins(): array
{
    // Keyed, so you can reconfigure one without rebuilding the list.
    $plugins = parent::plugins();
    $plugins['shield']->navigationGroup(NavigationGroup::General);

    return [...$plugins, 'live-preview' => LivePreviewPlugin::make()];
}

protected function userMenuItems(): array
{
    return [...parent::userMenuItems(), Action::make('maintenanceMode')…];
}
```

Views override the Laravel way — `resources/views/vendor/filament-brigada-cms/…` wins.
Classes can be swapped through the container. And everything above is togglable in config:

```bash
php artisan vendor:publish --tag=filament-brigada-cms-config
```

Add your project's own acronyms there rather than editing the package:

```php
'acronyms' => [..., 'bc', 'btw', 'kkg', 'ogm'],
```

## Panel error page

Point your `resources/views/errors/admin.blade.php` at the packaged one:

```blade
@include('filament-brigada-cms::errors.panel')
```

## Developing this package

Improvements are meant to be made *in the package*, while working on a real project —
not made in the project and cherry-picked back. Point the project at a local checkout:

```bash
composer config repositories.brigada-cms path ../filament-brigada-cms
composer require wotz/filament-brigada-cms:@dev
```

`vendor/wotz/filament-brigada-cms` is then a symlink to your checkout, so you edit the
package directly with the project running against it. Commit and tag in the package; every
other project picks it up on `composer update`.

Remove the path repository before committing the project:

```bash
composer config --unset repositories.brigada-cms
```

## Licence

MIT. See [LICENSE.md](LICENSE.md).
