<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Debt;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('debts.{id}', function (User $user, int $debt_id) {
    dump('in the channel');
    return $user->id === Debt::findOrFail($debt_id)->user_id;
});
