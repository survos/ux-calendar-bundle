<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'preact' => [
        'version' => '10.23.2',
    ],
    'preact/hooks' => [
        'version' => '10.23.2',
    ],
    'preact/jsx-runtime' => [
        'version' => '10.23.2',
    ],
    'preact/compat' => [
        'version' => '10.23.2',
    ],
    'preact/compat/client' => [
        'version' => '10.23.2',
    ],
    'temporal-polyfill/fns/zoneddatetime' => [
        'version' => '0.3.2',
    ],
    'temporal-polyfill/fns/plaindatetime' => [
        'version' => '0.3.2',
    ],
    'temporal-polyfill/fns/instant' => [
        'version' => '0.3.2',
    ],
    '@full-ui/headless-calendar' => [
        'version' => '7.0.0-beta.8',
    ],
    'fullcalendar' => [
        'version' => '7.0.0-beta.8',
    ],
    'fullcalendar/themes/classic' => [
        'version' => '7.0.0-beta.8',
    ],
    'fullcalendar/interaction' => [
        'version' => '7.0.0-beta.8',
    ],
    'fullcalendar/daygrid' => [
        'version' => '7.0.0-beta.8',
    ],
    'fullcalendar/timegrid' => [
        'version' => '7.0.0-beta.8',
    ],
    'fullcalendar/list' => [
        'version' => '7.0.0-beta.8',
    ],
];
