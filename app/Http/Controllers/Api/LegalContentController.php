<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LegalContentService;
use Illuminate\Http\JsonResponse;

class LegalContentController extends Controller
{
    public function show(LegalContentService $legalContent): JsonResponse
    {
        return response()->json([
            'content' => $legalContent->publicPayload(),
        ]);
    }
}
