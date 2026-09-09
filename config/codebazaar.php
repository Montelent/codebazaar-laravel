<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CodeBazaar product version (sales / admin display)
    |--------------------------------------------------------------------------
    | Keep in sync with the VERSION file and composer.json "version".
    */
    'version' => trim((string) (@file_get_contents(base_path('VERSION')) ?: '1.0.0')),

    'name' => 'CodeBazaar',

    'codename' => 'marketplace',
];
