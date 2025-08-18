<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GroupUser;
use App\Models\Debt;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Invite;

class Group extends Model
{
    /** @use HasFactory<\Database\Factories\GroupFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'user_id'
    ];

    /**
     * Group users for the group, created when a user joins/creates a group.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function group_users(): HasMany
    {
        return $this->hasMany(GroupUser::class);
    }

    /**
     * Users that belong to the group through group_users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            GroupUser::class,
            'group_id',    // Foreign key on GroupUser table...
            'id',          // Foreign key on User table...
            'id',          // Local key on Group table...
            'user_id'      // Local key on GroupUser table...
        );
    }
    
    /**
     * Debts for the group, owned by a group user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    /**
     * User that owns a group
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Invites sent out for the group.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invites(): HasMany
    {
        return $this->hasMany(Debt::class);
    }
}
