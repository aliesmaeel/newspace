<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InterestOption;
use Illuminate\Http\JsonResponse;

class InterestOptionController extends Controller
{
    public function index(): JsonResponse
    {
        $options = InterestOption::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'label'])
            ->values();

        return response()->json(['options' => $options]);
    }
}
