<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Format JSON response langganan yang konsisten di semua endpoint.
 *
 * Penggunaan: return new SubscriptionResource($subscription);
 *             return SubscriptionResource::collection($subscriptions);
 */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'plan'           => $this->plan,
            'status'         => $this->status->value,
            'status_label'   => $this->status->label(),
            'started_at'     => $this->started_at->toDateString(),
            'expires_at'     => $this->expires_at?->toDateString(),
            'remaining_days' => $this->remainingDays(),
            'is_lifetime'    => is_null($this->expires_at),
            'is_active'      => $this->isActive(),
        ];
    }
}
