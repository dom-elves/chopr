<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\AliasResource;

class GroupUserResource extends JsonResource
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
            'balance' => $this->balace,
            'user_id' => $this->user_id,
            'group_id' => $this->group_id,

            // permissions
            'can' => [
                'update' => $request->user()->can('update', $this->resource),
                'delete' => $request->user()->can('delete', $this->resource),
            ],

            // relationships
            // has to be a new UserResource as it's a belongsTo relationship
            // so only loads a single item
            'user' => new UserResource(
                $this->whenLoaded('user')
            ),

            'aliases' => AliasResource::collection(
                $this->whenLoaded('aliases')
            ),
        ];
    }
}
