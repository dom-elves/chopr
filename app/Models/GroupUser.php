<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Share;
use App\Models\User;
use App\Models\Group;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupUser extends Model
{
    /** @use HasFactory<\Database\Factories\GroupUserFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'group_id',
        'balance',
    ];

    protected $attributes = [
        'balance' => 0.00,
    ];

    /**
     * Shares for the group user. A share is their part of a debt.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    /**
     * User for a given group user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Group the group user belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
