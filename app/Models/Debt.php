<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Group;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\DebtObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Casts\Cash;
use Illuminate\Support\Facades\Auth;

class Debt extends Model
{
    /** @use HasFactory<\Database\Factories\DebtFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'group_id',
        'group_user_id',
        'name',
        'amount',
        'split_even',
        'cleared',
        'currency',
    ];

    protected $casts = [
        'amount' => Cash::class,
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
     * Group User that owns the debt.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group_user(): BelongsTo
    {
        return $this->belongsTo(GroupUser::class);
    }

    /**
     * Users in the debt.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function group_users(): HasManyThrough
    {
        return $this->hasManyThrough(
            GroupUser::class,    // end goal
            Share::class,   // middleman
            'debt_id',      // foreign key on middle man 
            'id',           // foreign key on end goal
            'id',           // local key on start
            'group_user_id'       // local key on middleman

            // so it's kinda like
            // start->middle local->foreign
            // middle->end local->foreign
        );
    }

    /**
     * Comments on the debt.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
