<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'group_user_id' => $this->group_user_id,
            'content' => $this->content,
            'edited' => $this->edited,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // permissions
            'can' => [
                'update' => $request->user()->can('update', $this->resource),
                'delete' => $request->user()->can('delete', $this->resource),
            ],

            // relationships
            'group_user' => new GroupUserResource(
                $this->whenLoaded('groupUser')
            ),
        ];
    }
}
