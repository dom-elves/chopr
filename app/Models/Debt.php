<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Group;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\Cash;
use App\Enums\DebtType;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

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
        'split_even' => DebtType::class,
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
    public function groupUser(): BelongsTo
    {
        return $this->belongsTo(GroupUser::class);
    }

    /**
     * Users in the debt.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function groupUsers(): HasManyThrough
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

    /**
     * Ledger entries for the debt.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function ledgerEntries(): HasManyThrough
    {
        return $this->hasManyThrough(
            LedgerEntry::class,
            Share::class,
            'debt_id',  
        );
    }

    /**
     * Scope to inclue all the debts a user is involed in:
     * - Debts they are the owner of, with or without a share.
     * - Debts they have a share in.
     */
    #[Scope]
    protected function involved(Builder $query, User $user): void
    {
        $query->whereIn('group_user_id', $user->groupUsers->pluck('id'))
            ->orWhereHas('shares', fn($q) => $q->whereIn('group_user_id', $user->groupUsers->pluck('id')))
            ->distinct();
    }
}
