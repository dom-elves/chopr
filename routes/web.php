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

Route::get('/dashboard', function (Request $request) {
    // todo: look up if it's better to send data like this
    // or to send in separate variables e.g. list of debts, groups etc
    // with minimal relationships, then map everything together on the FE
    $debts = $request->user()
        ->involvedDebts()
        ->with([
            'shares.group_user.user',
            'comments.user',
            'group.group_users.user',
        ])
        ->get();

    $groups = $request->user()
        ->groups()
        ->with('group_users.user')
        ->get();

    return Inertia::render('Dashboard', [
        'groups' => $groups,
        'debts' => $debts,
        'status' => $request->session()->get('status') ?? null,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/groups', function (Request $request) {
    $groups = $request->user()
        ->groups()
        ->with('group_users.user')
        ->get();
    
    return Inertia::render('Groups', [
        'groups' => $groups,
        'status' => $request->session()->get('status') ?? null,
    ]);
})->middleware(['auth', 'verified'])->name('groups');

// groups
Route::middleware('auth')->group(function () {
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

// debts
Route::middleware('auth')->group(function () {
    Route::get('/debt/index', [DebtController::class, 'index'])->name('debt.index');
    Route::post('/debt/store', [DebtController::class, 'store'])->name('debt.store');
    Route::patch('/debt/update', [DebtController::class, 'update'])->name('debt.update');
    Route::delete('/debt/destroy', [DebtController::class, 'destroy'])->name('debt.destroy');
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
        'user' => Auth::user() ? Auth::user() : 'no user here',
    ]);

})->middleware('auth');

Route::get('inertia-playground', function() {
    return Inertia::render('InertiaPlayground', [
        'inertiaVariable' => 'inertia variable text'
    ]);
});
require __DIR__.'/auth.php';
