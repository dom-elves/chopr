<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Requests\DeleteCommentRequest;
use App\Models\Comment;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $debt = Debt::findOrFail($validated['debt_id']);

        if ($request->user()->cannot('create', [Comment::class, $debt])) {
          
            return redirect()->route('debt.index')->withErrors(['debt_id' => 'You do not have permission to comment on this debt.']);
        }

        Comment::create([
            'debt_id' => $validated['debt_id'],
            'content' => $validated['content'],
            'user_id' => $validated['user_id'],
        ]);

        return redirect()->route('debt.index')->with('status', 'Comment added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse
    {
        if ($request->user()->cannot('update', $comment)) {
            return redirect()->route('debt.index')->withErrors(['content' => 'You do not have permission to edit this comment.']);
        }

        $validated = $request->validated();

        $comment->update([
            'content' => $validated['content'],
            'edited' => true,
        ]);

        return redirect()->route('debt.index')->with('status', 'Comment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        if ($request->user()->cannot('delete', $comment)) {
            return redirect()->route('debt.index')->withErrors(['id' => 'You do not have permission to delete this comment']);
        } 

        $comment->delete();

        return redirect()
            ->route('debt.index')
            ->with('status', 'Comment deleted successfully.');    
    }
}
