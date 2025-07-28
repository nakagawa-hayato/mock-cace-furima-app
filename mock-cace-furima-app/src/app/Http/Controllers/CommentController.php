<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    //
    public function store(Request $request)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
            'item_id' => 'required|exists:items,id',
        ]);

        Comment::create([
            'user_id' => auth()->id(),
            'item_id' => $validated['item_id'],
            'comment' => $validated['comment'],
        ]);

        return back()->with('status', 'コメントを投稿しました');
    }
}
