<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\JsonResponse;

class ProgramController extends Controller
{
    public function index(): JsonResponse
    {
        $programs = Program::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'slug',
                'title',
                'description',
                'image_url',
                'price_cents',
                'billing_interval_months',
            ])
            ->map(fn (Program $program): array => [
                'slug' => $program->slug,
                'title' => $program->title,
                'description' => $program->description,
                'image_url' => $this->resolveImageUrl($program->image_url),
                'price_cents' => (int) $program->price_cents,
                'billing_interval_months' => $program->billing_interval_months,
                'price_label' => $program->formattedPriceLabel(),
            ])
            ->values();

        return response()->json([
            'programs' => $programs,
        ]);
    }

    private function resolveImageUrl(?string $imagePath): ?string
    {
        $path = trim((string) $imagePath);
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return rtrim((string) config('app.url'), '/') . '/storage/' . ltrim($path, '/');
    }
}
