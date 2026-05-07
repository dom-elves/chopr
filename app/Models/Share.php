<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GroupUser;
use App\Models\Debt;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'group_user_id',
        'debt_id',
        'name',
        'amount',
        'sent',
        'seen',
    ];

    protected $casts = [
        'amount' => Cash::class,
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
     * Group user that owns the share.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function groupUser()
    {
        return $this->belongsTo(GroupUser::class); 
    }

    /**
     * Ledger entry for the share.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
