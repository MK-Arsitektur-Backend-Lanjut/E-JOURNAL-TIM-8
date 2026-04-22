<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExtendSubscriptionRequest;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MembershipController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $service,
        private readonly SubscriptionRepositoryInterface $repository
    ) {}

    /**
     * GET /api/membership/download-access
     */
    public function checkDownloadAccess(Request $request): JsonResponse
    {
        $userId  = $request->user()->id;
        $isValid = $this->repository->isValidForDownload($userId);

        if (! $isValid) {
            return response()->json([
                'allowed' => false,
                'message' => 'Langganan tidak aktif. Silakan perbarui langganan Anda.',
            ], 403);
        }

        return response()->json([
            'allowed'      => true,
            'message'      => 'Akses unduh tersedia.',
            'subscription' => new SubscriptionResource(
                $this->repository->findActiveByUser($userId)
            ),
        ]);
    }

    /**
     * GET /api/membership/history
     */
    public function history(Request $request): AnonymousResourceCollection
    {
        return SubscriptionResource::collection(
            $this->repository->getAllByUser($request->user()->id)
        );
    }

    /**
     * POST /api/membership/subscribe
     */
    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $result = $this->service->subscribe(
            $request->user(),
            $request->validated('plan')
        );

        return match ($result['status']) {
            'created'  => response()->json([
                'message'      => $result['message'],
                'subscription' => new SubscriptionResource($result['subscription']),
            ], 201),

            'extended' => response()->json([
                'message'      => $result['message'],
                'subscription' => new SubscriptionResource($result['subscription']),
            ]),

            'conflict' => response()->json([
                'message' => $result['message'],
                'current' => new SubscriptionResource($result['subscription']),
            ], 409),
        };
    }

    /**
     * PATCH /api/membership/{subscription}/cancel
     */
    public function cancel(Request $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('cancel', $subscription);

        $result = $this->service->cancel($subscription);

        return response()->json(
            ['message' => $result['message']],
            $result['success'] ? 200 : 422
        );
    }

    /**
     * PATCH /api/membership/{subscription}/extend
     */
    public function extend(ExtendSubscriptionRequest $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('extend', $subscription);

        $result = $this->service->extend($subscription, $request->integer('days'));

        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message'      => $result['message'],
            'subscription' => new SubscriptionResource($result['subscription']),
        ]);
    }
}
