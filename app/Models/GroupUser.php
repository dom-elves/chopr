<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Share;
use App\Models\User;
use App\Models\Group;
use App\Models\Alias;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Casts\Cash;
use Illuminate\Support\Facades\Auth;

class GroupUser extends Model
{
    /** @use HasFactory<\Database\Factories\GroupUserFactory> */
    use HasFactory;
    use SoftDeletes;

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

    protected $casts = [
        'balance' => Cash::class,
    ];

    protected $appends = [
        'can_update',
        'can_delete',
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

    /**
     * Aliases for the group user
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function aliases(): HasMany
    {
        return $this->hasMany(Alias::class);
    }

    public function getCanUpdateAttribute(): bool
    {
        $user = Auth::user();

        return $user->can('update', $this);
    }

    public function getCanDeleteAttribute(): bool
    {
        $user = Auth::user();

        return $user->can('delete', $this);
    }
}
