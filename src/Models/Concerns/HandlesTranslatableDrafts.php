<?php

namespace Wotz\FilamentBrigadaCms\Models\Concerns;

/**
 * Makes drafts keep translations.
 *
 * `laravel-drafts` copies a model's raw attributes into a revision. For a model using
 * `spatie/laravel-translatable` that means the JSON column is copied as whatever the
 * current locale resolved to, so every other locale is lost the moment a draft is saved.
 * Merging the full translation set back in keeps them.
 *
 * Use alongside `Oddvalue\LaravelDrafts\Concerns\HasDrafts` on any translatable model. Both
 * declare `getDraftableAttributes()`, so the collision has to be resolved explicitly or PHP
 * fatals:
 *
 *     use HandlesTranslatableDrafts {
 *         HandlesTranslatableDrafts::getDraftableAttributes insteadof HasDrafts;
 *     }
 *     use HasDrafts;
 */
trait HandlesTranslatableDrafts
{
    public function getDraftableAttributes(): array
    {
        return collect($this->getAttributes())
            ->merge(
                collect($this->getTranslatableAttributes())
                    ->mapWithKeys(fn ($attribute) => [$attribute => $this->getTranslations($attribute)]),
            )
            ->toArray();
    }
}
