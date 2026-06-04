<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Tag;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;

class CatalogLookupController extends Controller
{
    public function authors(): JsonResponse
    {
        // ✅ Cache authors - highly static, frequently accessed
        $authors = CacheService::getAuthors(function () {
            return Author::query()
                ->orderBy('name')
                ->get(['id', 'name']);
        });

        return response()->json([
            'success' => true,
            'data' => $authors,
        ]);
    }

    public function tags(): JsonResponse
    {
        // ✅ Cache tags - highly static, frequently accessed
        $tags = CacheService::getTags(function () {
            return Tag::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);
        });

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }
}
