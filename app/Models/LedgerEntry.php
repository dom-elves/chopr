<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Casts\Cash;


class LedgerEntry extends Model
{
    /** @use HasFactory<\Database\Factories\LedgerEntryFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'share_id',
        'amount',
        'type',
    ];

    protected $casts = [
        'amount' => Cash::class,
    ];

}
