<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ShareResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\GroupResource;


class DebtResource extends JsonResource
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
            'group_id' => $this->group_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'split_even' => $this->split_even,
            'cleared' => $this->cleared,
            'currency' => $this->currency,

            // permissions
            'can' => [
                'update' => $request->user()->can('update', $this->resource),
                'delete' => $request->user()->can('delete', $this->resource),
            ],

            //relationships
            'shares' => ShareResource::collection(
                $this->whenLoaded('shares')
            ),

            'comments' => CommentResource::collection(
                $this->whenLoaded('comments')
            ),

            'group' => new GroupResource(
                $this->whenLoaded('group')
            ),
        ];
    }
}
