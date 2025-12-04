<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GroupUser;
use App\Models\Debt;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\ShareObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Casts\Cash;
use Illuminate\Support\Facades\Auth;

class Share extends Model
{
    /** @use HasFactory<\Database\Factories\ShareFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'debt_id',
        'name',
        'amount',
        'sent',
        'seen',
    ];

    protected $casts = [
        'amount' => Cash::class,
    ];

    protected $appends = [
        'can_update_name',
        'can_update_amount',
        'can_update_sent',
        'can_update_seen',
        'can_delete',
    ];

    /**
     * Debt the share belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    /**
     * User that owns the share.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Group user that owns the share.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function group_user()
    {
        // takes the two foreign keys and figures out the relationship (laravel magic)
        return $this->hasOne(GroupUser::class, 'user_id', 'user_id'); 
    }

    /**
     * Append can_update policy to model
     * 
     * @return bool
     */
    public function getCanUpdateNameAttribute(): bool
    {
        $user = Auth::user();

        return $user->can('updateName', $this);
    }

    /**
     * Append can_update policy to model
     * 
     * @return bool
     */
    public function getCanUpdateAmountAttribute(): bool
    {
        $user = Auth::user();

        return $user->can('updateAmount', $this);
    }

    /**
     * Append can_update policy to model
     * 
     * @return bool
     */
    public function getCanUpdateSentAttribute(): bool
    {
        $user = Auth::user();

        return $user->can('updateSent', $this);
    }

    /**
     * Append can_update policy to model
     * 
     * @return bool
     */
    public function getCanUpdateSeenAttribute(): bool
    {
        $user = Auth::user();

        return $user->can('updateSeen', $this);
    }

    /**
     * Append can_delete policy to model
     * 
     * @return bool
     */
    public function getCanDeleteAttribute(): bool
    {
        $user = Auth::user();

        return $user->can('delete', $this);
    }
}
