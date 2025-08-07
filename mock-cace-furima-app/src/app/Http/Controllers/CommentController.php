<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Http\Requests\CommentRequest;

class CommentController extends Controller
{
    //
    public function store(CommentRequest $request)
    {
        Comment::create([
            'user_id' => auth()->id(),
            'item_id' => $request['item_id'],
            'comment' => $request['comment'],
        ]);

        return back()->with('status', 'コメントを投稿しました');
    }
}
