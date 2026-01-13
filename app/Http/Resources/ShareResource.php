<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\GroupResource;

class ShareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'debt_id' => $this->debt_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'sent' => $this->sent,
            'seen' => $this->seen,

            // permissions
            'can' => [
                'update_name' => $request->user()->can('updateName', $this->resource),
                'update_amount' => $request->user()->can('updateAmount', $this->resource),
                'update_sent' => $request->user()->can('updateSent', $this->resource),
                'update_seen' => $request->user()->can('updateSeen', $this->resource),
                'delete' => $request->user()->can('delete', $this->resource),
            ],

            // relationships
            'group_user' => new GroupUserResource(
                $this->whenLoaded('group_user')
            ),
        ];
    }
}
