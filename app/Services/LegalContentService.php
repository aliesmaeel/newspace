<?php

namespace App\Services;

use App\Models\LegalContent;

class LegalContentService
{
    public function get(): LegalContent
    {
        return LegalContent::query()->firstOrCreate(
            ['id' => 1],
            LegalContent::defaults()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function publicPayload(): array
    {
        $content = $this->get();
        $defaults = LegalContent::defaults();

        $payload = [];
        foreach (array_keys($defaults) as $key) {
            $value = $content->{$key};
            $payload[$key] = ($value === null || $value === '') ? $defaults[$key] : $value;
        }

        $brand = config('brand.name', 'SAKOUR Family Enterprise');
        if (empty($payload['controller_name'])) {
            $payload['controller_name'] = $brand;
        }

        return $payload;
    }
}
