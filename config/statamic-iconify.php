<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Prefixes
    |--------------------------------------------------------------------------
    |
    | Restrict which icon sets are available across the site. When set, only
    | these icon set prefixes will appear in search results and field config
    | dropdowns. Leave empty to allow all icon sets.
    |
    | Supports partial prefixes ending with "-" (e.g. "mdi-" matches "mdi-light").
    |
    | Example: ['mdi', 'tabler', 'ph', 'heroicons']
    |
    */

    'allowed_prefixes' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Categories
    |--------------------------------------------------------------------------
    |
    | Restrict icon sets to specific Iconify categories. When set, only icon
    | sets belonging to these categories will be available. Leave empty to
    | allow all categories.
    |
    | Available categories: Material, UI 24px, UI 16px / 32px, UI Multicolor,
    | UI Other / Mixed Grid, Thematic, Emoji, Logos, Flags / Maps,
    | Animated Icons, Archive / Unmaintained
    |
    | Example: ['UI 24px', 'Material']
    |
    */

    'allowed_categories' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Licenses
    |--------------------------------------------------------------------------
    |
    | Restrict icon sets to specific license types. When set, only icon sets
    | with matching licenses will be available. Leave empty to allow all.
    |
    | Uses the license title as shown in the Iconify API (e.g. "MIT",
    | "Apache 2.0", "CC BY 4.0").
    |
    | Example: ['MIT', 'Apache 2.0']
    |
    */

    'allowed_licenses' => [],

    /*
    |--------------------------------------------------------------------------
    | Default Store As
    |--------------------------------------------------------------------------
    |
    | The default storage format for new iconify fields.
    |
    | Supported: "name", "svg_data"
    |
    */

    'default_store_as' => 'name',

];
