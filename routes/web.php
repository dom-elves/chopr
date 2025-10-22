<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\InviteController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use \Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use Illuminate\Support\Facades\Auth;
use App\Models\Invite;

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

// remember to add ['auth', 'verified']

// debts
Route::middleware('auth')->group(function () {
    Route::get('/debts', [DebtController::class, 'index'])->name('debt.index');
    Route::post('/debts', [DebtController::class, 'store'])->name('debt.store');
    Route::patch('/debts', [DebtController::class, 'update'])->name('debt.update');
    Route::delete('/debts', [DebtController::class, 'destroy'])->name('debt.destroy');
});

// groups
Route::middleware('auth')->group(function () {
    Route::get('/groups', [GroupController::class, 'index'])->name('group.index');
    Route::post('/groups', [GroupController::class, 'store'])->name('group.store');
    Route::patch('/groups', [GroupController::class, 'update'])->name('group.update');
    Route::delete('/groups', [GroupController::class, 'destroy'])->name('group.destroy');
});

// ootb profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// shares
Route::middleware('auth')->group(function () {
    Route::post('/share', [ShareController::class, 'store'])->name('share.store');
    Route::delete('/share', [ShareController::class, 'destroy'])->name('share.destroy');
    Route::patch('/share', [ShareController::class, 'update'])->name('share.update');
});

// users
Route::middleware('auth')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

// group users
Route::middleware('auth')->group(function () {
    Route::patch('/group-users', [GroupUserController::class, 'update'])->name('group-users.update');
    Route::post('/group-users', [GroupUserController::class, 'store'])->name('group-users.store');
    Route::delete('/group-users', [GroupUserController::class, 'destroy'])->name('group-users.destroy');
});

// comments
Route::middleware('auth')->group(function () {
    Route::post('/comment', [CommentController::class, 'store'])->name('comment.store');
    Route::patch('/comment', [CommentController::class, 'update'])->name('comment.update');
    Route::delete('/comment', [CommentController::class, 'destroy'])->name('comment.destroy');
});

// mails
Route::get('/invite', [InviteController::class, 'index'])->name('invite.index');
Route::post('/invite', [InviteController::class, 'store'])->name('invite.send');
Route::get('/invite/accept/{token}', [InviteController::class, 'accept'])->name('invite.accept');

// testing/playground
Route::get('/playground', function() {

    return view('playground', [
        'test_variable' => 'just some text',
        'auth_user' => Auth::user() ? Auth::user() : 'no user',
        'user' => User::first(),
    ]);

});

Route::get('inertia-playground', function() {
    return Inertia::render('InertiaPlayground', [
        'inertiaVariable' => 'inertia variable text'
    ]);
});
require __DIR__.'/auth.php';
