<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class CatalogLookupController extends Controller
{
    public function authors(): JsonResponse
    {
        $authors = Author::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $authors,
        ]);
    }

    public function tags(): JsonResponse
    {
        $tags = Tag::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }
}

