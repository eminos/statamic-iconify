<?php

use Statamic\Facades\User;

beforeEach(function () {
    $this->user = User::make()
        ->id('test-user-id')
        ->email('test@example.com')
        ->data(['name' => 'Test User'])
        ->makeSuper();
    $this->user->save();
});

it('returns config endpoint with current settings', function () {
    config()->set('statamic-iconify.allowed_prefixes', ['mdi']);
    config()->set('statamic-iconify.allowed_categories', ['UI 24px']);
    config()->set('statamic-iconify.allowed_licenses', ['MIT']);
    config()->set('statamic-iconify.default_store_as', 'svg_data');

    $this->actingAs($this->user)
        ->getJson(cp_route('statamic-iconify.config'))
        ->assertOk()
        ->assertJsonPath('allowed_prefixes', ['mdi'])
        ->assertJsonPath('allowed_categories', ['UI 24px'])
        ->assertJsonPath('allowed_licenses', ['MIT'])
        ->assertJsonPath('default_store_as', 'svg_data');
});

it('returns empty config when defaults are used', function () {
    $this->actingAs($this->user)
        ->getJson(cp_route('statamic-iconify.config'))
        ->assertOk()
        ->assertJsonPath('allowed_prefixes', [])
        ->assertJsonPath('allowed_categories', [])
        ->assertJsonPath('allowed_licenses', [])
        ->assertJsonPath('default_store_as', 'name');
});

it('requires authentication for config endpoint', function () {
    $this->getJson(cp_route('statamic-iconify.config'))
        ->assertUnauthorized();
});
