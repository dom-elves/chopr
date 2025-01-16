<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DebtController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use \Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;

/*
* OOTB routes
*/
Route::get('/', function () {

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// store() in auth session controller is hit first, but it just redirects here
// not sure if it's necessary to farm this out into a controller for the dashboard
// or even have this route at all if the dashboard is just a component that can be returned
Route::get('/dashboard', function (Request $request) {
    // dump('first', Auth::check());
    $groups = Group::whereIn('id', $request->user()->group_users->pluck('group_id'))
        ->with(['group_users.user', 'debts.shares.group_user.user'])
        ->get();
    
    return Inertia::render('Dashboard', [
        'groups' => $groups,
        'status' => $request->status ?? null
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // dump('first', Auth::check());
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// debts
Route::middleware('auth')->group(function () {

    Route::post('/debt', [DebtController::class, 'store'])->name('debt.store');
    Route::delete('/debt', [DebtController::class, 'destroy'])->name('debt.destroy');
});

// testing/playground
Route::get('/playground', function() {
    return view('playground', [
        'test_variable' => 'just some text'
    ]);
})->middleware('auth');

Route::get('inertia-playground', function() {
    return Inertia::render('InertiaPlayground', [
        'inertiaVariable' => 'inertia variable text'
    ]);
});
require __DIR__.'/auth.php';
