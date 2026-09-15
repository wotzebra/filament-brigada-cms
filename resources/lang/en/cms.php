<?php

return [

    'onboarding' => [
        'menu' => 'Getting started',
    ],

    'read_only' => [
        'badge' => 'Read-only',
        'tooltip' => 'This resource is synced from an external system and cannot be modified here.',
    ],

    'navigation' => [
        'default_group' => 'General',
        'expand_all' => 'Expand all',
        'collapse_all' => 'Collapse all',
    ],

    'drafts' => [
        'publish' => 'Publish',
        'save_draft' => 'Save to new draft',
        'create_draft' => 'Create draft',
        'preview' => 'Preview',
        'state' => [
            'published' => 'Published',
            'draft' => 'Draft',
        ],
        'version_history' => 'Version history',
        'save' => 'Save draft',
        'version' => 'Version',
    ],

    'search' => [
        'placeholder' => 'Search',
    ],

    'errors' => [
        403 => ['heading' => 'No access', 'description' => 'You do not have permission to view this page. Ask an administrator if you think this is a mistake.'],
        404 => ['heading' => 'Page not found', 'description' => 'This page does not exist. It may have been moved or removed.'],
        419 => ['heading' => 'Your session has expired', 'description' => 'Please sign in again to continue.'],
        503 => ['heading' => 'Temporarily unavailable', 'description' => 'Maintenance is in progress. Please try again in a few minutes.'],
        'default' => ['heading' => 'Something went wrong', 'description' => 'An unexpected error occurred. The problem has been reported.'],
        'back' => 'Back to the dashboard',
    ],

];
