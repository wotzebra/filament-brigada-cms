<?php

return [

    'read_only' => [
        'badge' => 'Alleen-lezen',
        'tooltip' => 'Deze resource wordt gesynchroniseerd vanuit een extern systeem en kan hier niet aangepast worden.',
    ],

    'navigation' => [
        'default_group' => 'Algemeen',
        'expand_all' => 'Alles openklappen',
        'collapse_all' => 'Alles dichtklappen',
    ],

    'search' => [
        'placeholder' => 'Zoeken',
    ],

    'errors' => [
        403 => ['heading' => 'Geen toegang', 'description' => 'You do not have permission to view this page. Ask an administrator if you think this is a mistake.'],
        404 => ['heading' => 'Pagina niet gevonden', 'description' => 'This page does not exist. It may have been moved or removed.'],
        419 => ['heading' => 'Je sessie is verlopen', 'description' => 'Please sign in again to continue.'],
        503 => ['heading' => 'Tijdelijk niet beschikbaar', 'description' => 'Maintenance is in progress. Please try again in a few minutes.'],
        'default' => ['heading' => 'Er ging iets mis', 'description' => 'An unexpected error occurred. The problem has been reported.'],
        'back' => 'Terug naar het dashboard',
    ],

];
