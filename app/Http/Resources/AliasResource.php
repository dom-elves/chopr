<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AliasResource extends JsonResource
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
            'user_id' => $this->user_id,
            'group_user_id' => $this->group_user_id,
            'alias' => $this->alias,

            // permissions
            'can' => [
                'update' => $request->user()->can('update', $this->resource),
            ],
        ];
    }
}
