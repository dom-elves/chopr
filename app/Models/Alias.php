<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\GroupUser;

class Alias extends Model
{
    /** @use HasFactory<\Database\Factories\AliasFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'group_user_id', 
        'alias',
    ];

    /**
     * Group User that the alias is for.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function groupUser(): BelongsTo
    {
        return $this->belongsTo(GroupUser::class);
    }
}
