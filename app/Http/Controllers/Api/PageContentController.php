<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PageContentService;
use Illuminate\Http\JsonResponse;

class PageContentController extends Controller
{
    public function show(PageContentService $pageContent): JsonResponse
    {
        return response()->json([
            'content' => $pageContent->publicPayload(),
        ]);
    }
}
