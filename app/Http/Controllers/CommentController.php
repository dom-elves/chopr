<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Requests\DeleteCommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use App\Rules\IsCommentOwner;

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
    public function store(StoreCommentRequest $request)
    {
        $validated = $request->validated();

        $comment = Comment::create([
            'debt_id' => $validated['debt_id'],
            'content' => $validated['content'],
            'user_id' => $validated['user_id'],
        ]);

        return redirect()->route('dashboard')->with('status', 'Comment added successfully.');
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
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $validated = $request->validated();

        $comment = Comment::find($validated['id']);
        $comment->update([
            'content' => $validated['content'],
            'edited' => true,
        ]);

        return redirect()->route('dashboard')->with('status', 'Comment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:comments,id', new IsCommentOwner],
        ])->validate();
    
        Comment::where('id', $request->all())->delete();

        return redirect()->route('dashboard')->with('status', 'Comment deleted successfully.');
    }
}
