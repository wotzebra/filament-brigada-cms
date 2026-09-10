<?php

use Wotz\FilamentBrigadaCms\Tests\Fixtures\Models\TranslatableArticle;

it('keeps every locale when a translatable model is copied into a draft', function () {
    $article = new TranslatableArticle;
    $article->setTranslations('title', ['nl' => 'Dakpannen', 'fr' => 'Tuiles']);

    $draft = new TranslatableArticle;
    $draft->forceFill($article->getDraftableAttributes());

    expect($draft->getTranslations('title'))->toBe(['nl' => 'Dakpannen', 'fr' => 'Tuiles']);
});

it('hands the draft an array rather than the raw JSON a revision would nest', function () {
    app()->setLocale('fr');

    $article = new TranslatableArticle;
    $article->setTranslations('title', ['nl' => 'Dakpannen', 'fr' => 'Tuiles']);

    /*
     * This is the whole reason the trait exists. `laravel-drafts` copies raw attributes
     * into the revision, and a raw translatable attribute is a JSON string — which
     * `spatie/laravel-translatable` reads back as a value for the current locale and
     * stores nested under it, so every other locale is gone the first time a draft is
     * saved.
     */
    expect($article->getAttributes()['title'])->toBeString()
        ->and($article->getDraftableAttributes()['title'])->toBe(['nl' => 'Dakpannen', 'fr' => 'Tuiles']);
});

it('leaves untranslated attributes exactly as they were', function () {
    $article = new TranslatableArticle;
    $article->amount_including_vat = '12,10';

    expect($article->getDraftableAttributes()['amount_including_vat'])->toBe('12,10');
});
