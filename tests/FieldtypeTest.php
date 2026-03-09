<?php

use StatamicIconify\Fieldtypes\IconifyFieldtype;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::forget('statamic-iconify.collections');

    Http::fake([
        'api.iconify.design/collections' => Http::response([
            'mdi' => [
                'name' => 'Material Design Icons',
                'total' => 7134,
                'category' => 'General',
                'license' => ['title' => 'Apache 2.0', 'spdx' => 'Apache-2.0'],
            ],
            'tabler' => [
                'name' => 'Tabler Icons',
                'total' => 2925,
                'category' => 'UI 24px',
                'license' => ['title' => 'MIT', 'spdx' => 'MIT'],
            ],
        ], 200),
    ]);
});

it('has the correct config field items', function () {
    $fieldtype = new IconifyFieldtype();
    $reflection = new ReflectionMethod($fieldtype, 'configFieldItems');
    $reflection->setAccessible(true);
    $configFields = $reflection->invoke($fieldtype);

    expect($configFields)->toHaveKeys(['prefixes', 'category', 'licenses', 'store_as']);
    expect($configFields['prefixes']['type'])->toBe('select');
    expect($configFields['prefixes']['multiple'])->toBeTrue();
    expect($configFields['category']['type'])->toBe('select');
    expect($configFields['category']['clearable'])->toBeTrue();
    expect($configFields['store_as']['type'])->toBe('button_group');
});

it('populates prefix options from API', function () {
    $fieldtype = new IconifyFieldtype();
    $reflection = new ReflectionMethod($fieldtype, 'configFieldItems');
    $reflection->setAccessible(true);
    $configFields = $reflection->invoke($fieldtype);

    $prefixOptions = $configFields['prefixes']['options'];

    expect($prefixOptions)->toHaveKey('mdi');
    expect($prefixOptions)->toHaveKey('tabler');
    expect($prefixOptions['mdi'])->toContain('Material Design Icons');
    expect($prefixOptions['mdi'])->toContain('mdi');
});

it('populates category options from API', function () {
    $fieldtype = new IconifyFieldtype();
    $reflection = new ReflectionMethod($fieldtype, 'configFieldItems');
    $reflection->setAccessible(true);
    $configFields = $reflection->invoke($fieldtype);

    $categoryOptions = $configFields['category']['options'];

    expect($categoryOptions)->toHaveKey('General');
    expect($categoryOptions)->toHaveKey('UI 24px');
});

it('populates license options from API', function () {
    $fieldtype = new IconifyFieldtype();
    $reflection = new ReflectionMethod($fieldtype, 'configFieldItems');
    $reflection->setAccessible(true);
    $configFields = $reflection->invoke($fieldtype);

    $licenseOptions = $configFields['licenses']['options'];

    expect($licenseOptions)->toHaveKey('Apache 2.0');
    expect($licenseOptions)->toHaveKey('MIT');
    expect($configFields['licenses']['multiple'])->toBeTrue();
});

it('filters prefix options by global config', function () {
    config()->set('statamic-iconify.allowed_prefixes', ['mdi']);

    $fieldtype = new IconifyFieldtype();
    $reflection = new ReflectionMethod($fieldtype, 'configFieldItems');
    $reflection->setAccessible(true);
    $configFields = $reflection->invoke($fieldtype);

    $prefixOptions = $configFields['prefixes']['options'];

    expect($prefixOptions)->toHaveKey('mdi');
    expect($prefixOptions)->not->toHaveKey('tabler');
});

it('filters category options by global config', function () {
    config()->set('statamic-iconify.allowed_categories', ['UI 24px']);

    $fieldtype = new IconifyFieldtype();
    $reflection = new ReflectionMethod($fieldtype, 'configFieldItems');
    $reflection->setAccessible(true);
    $configFields = $reflection->invoke($fieldtype);

    $categoryOptions = $configFields['category']['options'];
    $prefixOptions = $configFields['prefixes']['options'];

    expect($categoryOptions)->toHaveKey('UI 24px');
    expect($categoryOptions)->not->toHaveKey('General');
    expect($prefixOptions)->toHaveKey('tabler');
    expect($prefixOptions)->not->toHaveKey('mdi');
});

it('filters options by allowed licenses in global config', function () {
    config()->set('statamic-iconify.allowed_licenses', ['MIT']);

    $fieldtype = new IconifyFieldtype();
    $reflection = new ReflectionMethod($fieldtype, 'configFieldItems');
    $reflection->setAccessible(true);
    $configFields = $reflection->invoke($fieldtype);

    $prefixOptions = $configFields['prefixes']['options'];
    $licenseOptions = $configFields['licenses']['options'];

    // Only tabler has MIT license
    expect($prefixOptions)->toHaveKey('tabler');
    expect($prefixOptions)->not->toHaveKey('mdi');
    expect($licenseOptions)->toHaveKey('MIT');
    expect($licenseOptions)->not->toHaveKey('Apache 2.0');
});

it('filters by all three global config options combined', function () {
    config()->set('statamic-iconify.allowed_categories', ['General']);
    config()->set('statamic-iconify.allowed_licenses', ['Apache 2.0']);

    $fieldtype = new IconifyFieldtype();
    $reflection = new ReflectionMethod($fieldtype, 'configFieldItems');
    $reflection->setAccessible(true);
    $configFields = $reflection->invoke($fieldtype);

    $prefixOptions = $configFields['prefixes']['options'];

    // mdi is General + Apache 2.0
    expect($prefixOptions)->toHaveKey('mdi');
    // tabler is UI 24px (not General) — excluded
    expect($prefixOptions)->not->toHaveKey('tabler');
});

it('has null as default value', function () {
    $fieldtype = new IconifyFieldtype();
    expect($fieldtype->defaultValue())->toBeNull();
});

it('passes data through preProcess unchanged', function () {
    $fieldtype = new IconifyFieldtype();
    expect($fieldtype->preProcess('mdi:home'))->toBe('mdi:home');
    expect($fieldtype->preProcess(null))->toBeNull();
    expect($fieldtype->preProcess(['body' => '<path/>', 'name' => 'test']))->toBe(['body' => '<path/>', 'name' => 'test']);
});

it('passes data through process unchanged', function () {
    $fieldtype = new IconifyFieldtype();
    expect($fieldtype->process('mdi:home'))->toBe('mdi:home');
    expect($fieldtype->process(null))->toBeNull();
});
