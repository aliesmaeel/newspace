<?php

namespace App\Services;

use App\Models\PageContent;

class PageContentService
{
    public function get(): PageContent
    {
        return PageContent::query()->firstOrCreate(
            ['id' => 1],
            PageContent::defaults()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function publicPayload(): array
    {
        $content = $this->get();
        $defaults = PageContent::defaults();

        $payload = [];
        foreach (array_keys($defaults) as $key) {
            $value = $content->{$key};
            $payload[$key] = $value ?? $defaults[$key];
        }

        if ($payload['about_eyebrow'] === null) {
            $payload['about_eyebrow'] = 'About ' . config('brand.name', 'SAKOUR Family Enterprise');
        }

        if ($payload['founder_role'] === null) {
            $payload['founder_role'] = 'Founder, ' . config('brand.name', 'SAKOUR Family Enterprise');
        }

        $payload['founder_credentials'] = $this->normalizeStringList($payload['founder_credentials'] ?? []);

        $payload['hero_banner_url'] = IntegrationSettingsService::resolvePublicAssetUrl(
            trim((string) ($payload['hero_banner_path'] ?? '')),
            '/assets/banner-bg.png'
        );

        $payload['mission_banner_url'] = IntegrationSettingsService::resolvePublicAssetUrl(
            trim((string) ($payload['mission_banner_path'] ?? '')),
            '/assets/giving-back-bg.png'
        );

        return $payload;
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, string>
     */
    private function normalizeStringList(array $items): array
    {
        return array_values(array_filter(array_map(function (mixed $item): ?string {
            if (is_string($item)) {
                return $item;
            }

            if (! is_array($item)) {
                return null;
            }

            foreach (['credential', 'value', 'text'] as $key) {
                if (isset($item[$key]) && is_string($item[$key])) {
                    return $item[$key];
                }
            }

            return null;
        }, $items)));
    }
}
