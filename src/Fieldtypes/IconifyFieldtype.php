<?php

namespace StatamicIconify\Fieldtypes;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Statamic\Fields\Fieldtype;

class IconifyFieldtype extends Fieldtype
{
    protected $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M2.857 19.429V9.642q.35-.089.67-.295l1.616-1.045v9.984h4.571v2.285H4c-.63 0-1.143-.512-1.143-1.142m7.429-8.572a1.715 1.715 0 1 1 3.43.001a1.715 1.715 0 0 1-3.43 0m-5.143-8V1.143a1.143 1.143 0 0 1 2.286 0v.235zm9.143 15.429h4.571V8.302l1.616 1.045q.32.206.67.295v9.787c0 .63-.512 1.143-1.143 1.143h-5.714Zm-.004-17.028l8.053 5.211a1.144 1.144 0 0 1-1.242 1.919l-8.041-5.203l.19-.123a2.28 2.28 0 0 0 1.04-1.804M11.38.183a1.144 1.144 0 0 1 1.242 1.92L2.907 8.387a1.144 1.144 0 0 1-1.242-1.919Zm2.6 22.103H22c.473 0 .857.384.857.857A.86.86 0 0 1 22 24H2a.86.86 0 0 1-.857-.857c0-.473.384-.857.857-.857h8.02a2.286 2.286 0 0 0 3.96 0m-.836-8.81v7.667a1.143 1.143 0 0 1-2.286 0v-7.667a2.85 2.85 0 0 0 1.143.238c.406 0 .793-.085 1.143-.238"/></svg>';

    /**
     * The blank/default value.
     *
     * @return array
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param  mixed  $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        return $data;
    }

    /**
     * Process the data before it gets saved.
     *
     * @param  mixed  $data
     * @return array|mixed
     */
    public function process($data)
    {
        return $data;
    }

    protected function configFieldItems(): array
    {
        $collections = $this->getFilteredCollections();

        return [
            'prefixes' => [
                'display' => 'Icon Sets',
                'instructions' => 'Limit search to specific icon sets. Leave empty to use all sets allowed by the global config.',
                'type' => 'select',
                'multiple' => true,
                'taggable' => true,
                'options' => $this->buildPrefixOptions($collections),
                'width' => 50,
            ],
            'category' => [
                'display' => 'Category',
                'instructions' => 'Limit search to icon sets from a specific category.',
                'type' => 'select',
                'clearable' => true,
                'placeholder' => 'All categories',
                'options' => $this->buildCategoryOptions($collections),
                'width' => 50,
            ],
            'licenses' => [
                'display' => 'Licenses',
                'instructions' => 'Limit search to icon sets with specific licenses. Leave empty for all.',
                'type' => 'select',
                'multiple' => true,
                'taggable' => true,
                'options' => $this->buildLicenseOptions($collections),
                'width' => 50,
            ],
            'store_as' => [
                'display' => 'Store icon as',
                'instructions' => 'Choose how the selected icon should be stored.',
                'type' => 'button_group',
                'default' => config('statamic-iconify.default_store_as', 'name'),
                'options' => [
                    'name' => 'Icon name',
                    'svg_data' => 'SVG data',
                ],
                'width' => 50,
            ],
        ];
    }

    /**
     * Fetch collections from Iconify API, filtered by global config.
     */
    protected function getFilteredCollections(): array
    {
        $all = Cache::remember('statamic-iconify.collections', 86400, function () {
            try {
                $response = Http::timeout(10)->get('https://api.iconify.design/collections');

                return $response->successful() ? $response->json() : [];
            } catch (\Exception $e) {
                return [];
            }
        });

        $allowedPrefixes = config('statamic-iconify.allowed_prefixes', []);
        $allowedCategories = config('statamic-iconify.allowed_categories', []);
        $allowedLicenses = config('statamic-iconify.allowed_licenses', []);

        if (empty($allowedPrefixes) && empty($allowedCategories) && empty($allowedLicenses)) {
            return $all;
        }

        return collect($all)->filter(function ($info, $prefix) use ($allowedPrefixes, $allowedCategories, $allowedLicenses) {
            if (! empty($allowedPrefixes) && ! $this->matchesPrefix($prefix, $allowedPrefixes)) {
                return false;
            }

            if (! empty($allowedCategories) && ! in_array($info['category'] ?? '', $allowedCategories)) {
                return false;
            }

            if (! empty($allowedLicenses) && ! in_array($info['license']['title'] ?? '', $allowedLicenses)) {
                return false;
            }

            return true;
        })->all();
    }

    /**
     * Build options for the prefix select: "Material Design Icons (mdi)" => "mdi"
     */
    protected function buildPrefixOptions(array $collections): array
    {
        return collect($collections)
            ->map(fn ($info, $prefix) => [
                'value' => $prefix,
                'label' => ($info['name'] ?? $prefix).' ('.$prefix.')',
            ])
            ->sortBy('label')
            ->pluck('label', 'value')
            ->all();
    }

    /**
     * Build options for the category select.
     */
    protected function buildCategoryOptions(array $collections): array
    {
        return collect($collections)
            ->pluck('category')
            ->filter()
            ->unique()
            ->sort()
            ->mapWithKeys(fn ($cat) => [$cat => $cat])
            ->all();
    }

    /**
     * Build options for the license select.
     */
    protected function buildLicenseOptions(array $collections): array
    {
        return collect($collections)
            ->map(fn ($info) => $info['license']['title'] ?? null)
            ->filter()
            ->unique()
            ->sort()
            ->mapWithKeys(fn ($license) => [$license => $license])
            ->all();
    }

    /**
     * Check if a prefix matches the allowed prefixes list.
     */
    protected function matchesPrefix(string $prefix, array $allowedPrefixes): bool
    {
        foreach ($allowedPrefixes as $allowed) {
            if ($allowed === $prefix) {
                return true;
            }

            if (str_ends_with($allowed, '-') && str_starts_with($prefix, $allowed)) {
                return true;
            }
        }

        return false;
    }
}
