<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Contracts\DocumentRepositoryInterface;

class AdvancedSearchController extends Controller
{
    protected $repository;

    public function __construct(DocumentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Advanced Search API (year, author, abstract)
     */
    public function search(Request $request)
    {
        $filters = $request->only(['year', 'author', 'abstract', 'title']);
        $perPage = (int) $request->input('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;
        $results = $this->repository->search($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Get recommendations for a document
     */
    public function recommendations($id)
    {
        $recommendations = $this->repository->getRecommendations($id);

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }
}
