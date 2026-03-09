<?php

use StatamicIconify\Tags\IconifyTag;

it('renders svg from array data', function () {
    $tag = app(IconifyTag::class);

    $context = [
        'icon' => [
            'body' => '<path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>',
            'attributes' => [
                'width' => '24',
                'height' => '24',
                'viewBox' => '0 0 24 24',
            ],
            'name' => 'mdi:home',
        ],
    ];

    $tag->setContext($context);
    $tag->setParameters([]);

    $result = $tag->wildcard('icon');

    expect($result)->toContain('<svg');
    expect($result)->toContain('xmlns="http://www.w3.org/2000/svg"');
    expect($result)->toContain('<path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>');
    expect($result)->toContain('width="24"');
});

it('returns string value for icon name', function () {
    $tag = app(IconifyTag::class);

    $context = [
        'icon' => 'mdi:home',
    ];

    $tag->setContext($context);
    $tag->setParameters([]);

    $result = $tag->wildcard('icon');

    expect($result)->toBe('mdi:home');
});

it('returns null for missing field', function () {
    $tag = app(IconifyTag::class);
    $tag->setContext([]);
    $tag->setParameters([]);

    $result = $tag->wildcard('icon');

    expect($result)->toBeNull();
});

it('merges additional params into svg attributes', function () {
    $tag = app(IconifyTag::class);

    $context = [
        'icon' => [
            'body' => '<path d="M10 20"/>',
            'attributes' => [
                'width' => '24',
                'height' => '24',
                'viewBox' => '0 0 24 24',
            ],
            'name' => 'test',
        ],
    ];

    $tag->setContext($context);
    $tag->setParameters(['class' => 'text-red-500', 'aria-label' => 'Home icon']);

    $result = $tag->wildcard('icon');

    expect($result)->toContain('class="text-red-500"');
    expect($result)->toContain('aria-label="Home icon"');
});
