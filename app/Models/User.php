<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\GroupUser;
use App\Models\Group;
use App\Models\Comment;
use App\Models\Debt;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Group users for the user, these are the groups that the user is a member of.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function group_users(): HasMany
    {
        return $this->hasMany(GroupUser::class);
    }

    /**
     * This is how the total balance for a user is calced
     * If this is ever changed, DebtFactory will need to have changes reverted
     * Probably loads of other stuff too
     */
    protected function userBalance(): Attribute
    {
        return Attribute::get(function () {
            return $this->group_users->sum('balance');
        });
    }

    /**
     * Groups for the user
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        // group_users is the table used as a pivot
        // user_id as key for user
        // group_id as key for group
        // essentially works as a 'link'
        return $this->belongsToMany(Group::class, 'group_users', 'user_id', 'group_id');
    }

    /**
     * Comments made on a debt by a user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Debts owner by a user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    /**
     * Shares owner by a user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }
}
