<?php

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Wotz\FilamentBrigadaCms\Support\Labels;

it('upper-cases an acronym that Filament would have lower-cased', function () {
    // "Amount including vat" is what Filament derives on its own, and reads as a typo
    // to everyone who opens the panel.
    expect(Labels::humanise('amount_including_vat'))->toBe('Amount including VAT')
        ->and(Labels::humanise('image_id'))->toBe('Image ID')
        ->and(Labels::humanise('seo_title'))->toBe('SEO title');
});

it('leaves a word alone when it merely contains an acronym', function () {
    // \b in the pattern: "id" inside "video" is not an id.
    expect(Labels::humanise('video_url'))->toBe('Video URL')
        ->and(Labels::humanise('idea'))->toBe('Idea')
        ->and(Labels::humanise('validation'))->toBe('Validation');
});

it('takes its acronyms from config so a project can add its own', function () {
    config()->set('filament-brigada-cms.acronyms', ['kkg']);

    expect(Labels::humanise('kkg_number'))->toBe('KKG number')
        // Only what the project listed: the defaults are gone with the config value.
        ->and(Labels::humanise('vat_number'))->toBe('Vat number');
});

it('humanises a name with no acronym in it at all', function () {
    config()->set('filament-brigada-cms.acronyms', []);

    expect(Labels::humanise('published_at'))->toBe('Published at')
        ->and(Labels::humanise('publishedAt'))->toBe('Published at');
});

it('labels a column, an entry and a field the same way', function () {
    expect(TextColumn::make('amount_including_vat')->getLabel())->toBe('Amount including VAT')
        ->and(TextEntry::make('amount_including_vat')->getLabel())->toBe('Amount including VAT')
        ->and(TextInput::make('amount_including_vat')->getLabel())->toBe('Amount including VAT');
});

it('lets an explicit label win over the humanised default', function () {
    expect(TextColumn::make('vat_rate')->label('Btw-tarief')->getLabel())->toBe('Btw-tarief')
        ->and(TextInput::make('vat_rate')->label('Btw-tarief')->getLabel())->toBe('Btw-tarief');
});

it('leaves labels to Filament when a project turns humanising off', function () {
    $this->rebootWithConfig(['filament-brigada-cms.humanise_labels' => false]);

    expect(TextColumn::make('amount_including_vat')->getLabel())->toBe('Amount including vat')
        ->and(TextInput::make('amount_including_vat')->getLabel())->toBe('Amount including vat');
});

it('names a field after its own segment, not the one above it', function () {
    /*
     * A component's name is often a state path rather than a bare attribute:
     * `data.title`, or `form.nl.title` for a field inside translatable tabs.
     * Taking the second-to-last segment labelled those "Data" and "Nl" — every
     * translated field in a panel named after its locale.
     */
    expect(Labels::humanise('data.title'))->toBe('Title')
        ->and(Labels::humanise('form.nl.working_title'))->toBe('Working title')
        ->and(Labels::humanise('title'))->toBe('Title');
});
