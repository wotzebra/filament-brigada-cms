<?php

use Filament\Navigation\NavigationGroup;

it('collapses navigation groups so a long sidebar stays readable', function () {
    expect(NavigationGroup::make('Content')->isCollapsed())->toBeTrue();
});

it('names an unlabelled group rather than leaving a gap in the sidebar', function () {
    // A resource that never set a navigation group still ends up in one, and an untitled
    // group draws as an unexplained rule above the first item.
    expect(NavigationGroup::make()->getLabel())->toBe('General');
});

it('keeps the label a group gave itself', function () {
    expect(NavigationGroup::make('Content')->getLabel())->toBe('Content');
});

it('leaves navigation groups untouched when a project turns collapsing off', function () {
    $this->rebootWithConfig(['filament-brigada-cms.navigation.collapse_groups' => false]);

    expect(NavigationGroup::make('Content')->isCollapsed())->toBeFalse()
        // The default label rides along with the same toggle.
        ->and(NavigationGroup::make()->getLabel())->toBeNull();
});
