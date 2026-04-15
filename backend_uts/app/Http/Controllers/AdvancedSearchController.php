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
        $results = $this->repository->search($filters);

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
