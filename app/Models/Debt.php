<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Group;
use App\Models\GroupUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
{
    /** @use HasFactory<\Database\Factories\DebtFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'group_id',
        'collector_group_user_id',
        'name',
        'amount',
        'split_even',
        'cleared',
        'currency',
    ];

    /**
     * Shares for the debt.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    /**
     * Group the debt belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Group user that owns the debt.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group_user(): BelongsTo
    {
        return $this->belongsTo(GroupUser::class);
    }
}
