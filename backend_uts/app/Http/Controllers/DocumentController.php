<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentRepositoryInterface $repository)
    {
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['title', 'year', 'author', 'tag']);
        $perPage = (int) request()->input('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        $documents = $this->repository->getAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $document = $this->repository->findById($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $document,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author_id' => ['nullable', 'integer', 'exists:authors,id'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'abstract' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('documents', 'public');
        }

        $document = $this->repository->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Document created successfully.',
            'data' => $document,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author_id' => ['sometimes', 'nullable', 'integer', 'exists:authors,id'],
            'year' => ['sometimes', 'required', 'integer', 'min:1900', 'max:2100'],
            'abstract' => ['sometimes', 'nullable', 'string'],
            'file' => ['sometimes', 'nullable', 'file', 'max:10240'],
            'tag_ids' => ['sometimes', 'nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ]);

        $existingDocument = $this->repository->findById($id);
        if (!$existingDocument) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.',
            ], 404);
        }

        if ($request->hasFile('file')) {
            if (!empty($existingDocument->file_path) && Storage::disk('public')->exists($existingDocument->file_path)) {
                Storage::disk('public')->delete($existingDocument->file_path);
            }

            $validated['file_path'] = $request->file('file')->store('documents', 'public');
        }

        $document = $this->repository->update($id, $validated);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Document updated successfully.',
            'data' => $document,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->repository->delete($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
        ]);
    }
}
