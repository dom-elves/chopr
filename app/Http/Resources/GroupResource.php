<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\GroupUserResource;

class GroupResource extends JsonResource
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
            'name' => $this->name,
            'user_id' => $this->user_id,

            // permissions
            'can' => [
                'update' => $request->user()->can('update', $this->resource),
                'invite' => $request->user()->can('invite', $this->resource),
                'delete' => $request->user()->can('delete', $this->resource),
            ],

            // relationships
            // over time, more of these will likely be filled out
            // as & when  more relationships are needed
            'group_users' => GroupUserResource::collection(
                $this->whenLoaded('groupUsers')
            ),
        ];
    }
}
