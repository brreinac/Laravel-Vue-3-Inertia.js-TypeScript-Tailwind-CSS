<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Task;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Task $task): CommentResource
    {
        $this->authorize('comment', $task);

        $comment = Comment::create([
            'body' => $request->validated('body'),
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
        ]);

        return new CommentResource($comment->load('user'));
    }
}
