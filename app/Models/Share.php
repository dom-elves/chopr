<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GroupUser;
use App\Models\Debt;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\ShareObserver;

#[ObservedBy([ShareObserver::class])]
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
}
