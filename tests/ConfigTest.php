<?php

it('has default config values', function () {
    expect(config('statamic-iconify.allowed_prefixes'))->toBe([]);
    expect(config('statamic-iconify.allowed_categories'))->toBe([]);
    expect(config('statamic-iconify.allowed_licenses'))->toBe([]);
    expect(config('statamic-iconify.default_store_as'))->toBe('name');
});

it('merges custom config values', function () {
    config()->set('statamic-iconify.allowed_prefixes', ['mdi', 'tabler']);
    config()->set('statamic-iconify.allowed_categories', ['UI 24px']);
    config()->set('statamic-iconify.allowed_licenses', ['MIT', 'Apache 2.0']);

    expect(config('statamic-iconify.allowed_prefixes'))->toBe(['mdi', 'tabler']);
    expect(config('statamic-iconify.allowed_categories'))->toBe(['UI 24px']);
    expect(config('statamic-iconify.allowed_licenses'))->toBe(['MIT', 'Apache 2.0']);
});
