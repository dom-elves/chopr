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
use App\Models\Invite;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Brick\Money\MoneyBag;
use Brick\Money\Money;
use Brick\Money\CurrencyConverter;

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
     * Appends custom cast when sending model to the frontend.
     * 
     * @var list<string>
     */
    protected $appends = [
        'user_balance'
    ];

    /**
     * Group users for the user, these are the groups that the user is a member of.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupUsers(): HasMany
    {
        return $this->hasMany(GroupUser::class);
    }

    /**
     * This is how the total balance for a user is calced
     * Currently defaulted to GBP for dev purposes
     * But can/will be changed in the future when exchange is implemented
     */
    protected function userBalance(): Attribute
    {
        return Attribute::get(function () {
            // if ($this->groupUsers->isEmpty()) {
            //     return Money::of(0, 'GBP');
            // } else {
            //     return $this->groupUsers->reduce(function (?Money $carry, GroupUser $group_user) {
            //         // sets the carry as the first group_user balance
            //         if ($carry === null) {
            //             return $group_user->balance;
            //         }
            //         return $carry->plus($group_user->balance);
            //     }, null);
            // }
            return 0;
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
        return $this->belongsToMany(Group::class, 'group_users', 'user_id', 'group_id')
            ->wherePivotNull('deleted_at');
    }

    /**
     * Comments made on a debt by a group user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function comments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, GroupUser::class, 'user_id', 'group_user_id');
    }

    /**
     * Debts owner by a group user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function debts(): HasManyThrough
    {
        return $this->hasManythrough(Debt::class, GroupUser::class, 'user_id', 'group_user_id');
    }

    /**
     * Shares owner by a user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function shares(): HasManyThrough
    {
        return $this->hasManyThrough(Share::class, GroupUser::class, 'user_id', 'group_user_id');
    }

    /**
     * Invites sent out by a user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class);
    }
}
