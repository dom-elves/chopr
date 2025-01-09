<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GroupUser;
use App\Models\Debt;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Share extends Model
{
    /** @use HasFactory<\Database\Factories\ShareFactory> */
    use HasFactory;

    protected $fillable = [
        'group_user_id',
        'debt_id',
        'amount',
        'paid_amount',
        'cleared',
    ];

    /**
     * Debt the share belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    /**
     * Group user that owns the share.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group_user(): BelongsTo
    {
        return $this->belongsTo(GroupUser::class);
    }
}
