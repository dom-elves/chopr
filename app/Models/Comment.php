<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Debt;
use App\Models\GroupUser;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * Debt the comment is on.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    /**
     * Group user that made the comment.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group_user(): BelongsTo
    {
        return $this->belongsTo(GroupUser::class);
    }
}
