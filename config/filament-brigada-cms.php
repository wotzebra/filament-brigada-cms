<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Acronyms
    |--------------------------------------------------------------------------
    |
    | Filament derives labels from component names, which lower-cases acronyms
    | ("Amount including vat"). Words listed here are upper-cased again. Add
    | your project's own — VAT and SEO are everyone's, KKG and OGM are not.
    |
    */

    'acronyms' => [
        'api', 'crm', 'css', 'csv', 'eu', 'html', 'id', 'json',
        'pdf', 'seo', 'sku', 'url', 'vat', 'vip', 'xml',
    ],

    'humanise_labels' => true,

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    |
    | `non_clickable_rows` keeps cell text selectable so an article number or a
    | title can be copied straight out of the table; records open through each
    | row's own actions instead.
    |
    */

    'tables' => [
        'enabled' => true,
        'non_clickable_rows' => true,
        'persist_state' => true,
        'pagination_options' => [10, 20, 50],
        'default_pagination' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Display formats
    |--------------------------------------------------------------------------
    */

    'formats' => [
        'date_time' => 'd M Y H:i:s',
        'date' => 'd M Y',
        'currency' => 'EUR',
    ],

    'navigation' => [
        'collapse_groups' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Onboarding
    |--------------------------------------------------------------------------
    |
    | The guided journeys a new editor is walked through. Four ship with this
    | package, describing the panel every Brigada CMS has; `onboarding:import`
    | applies those and then the project's own `database/onboarding/`, where a
    | file of the same name replaces a shipped one.
    |
    | `locales` are the languages a journey may be written in — usually the ones
    | the site publishes in. Left empty, the plugin's own default stands.
    |
    */

    'onboarding' => [
        'enabled' => true,
        'user_menu' => true,
        'locales' => [],
    ],

];
