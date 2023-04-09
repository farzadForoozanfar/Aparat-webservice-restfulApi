<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\ChangeStateCommentRequest;
use App\Services\CommentService;

class CommentController extends Controller
{
    public function Index (ChangeStateCommentRequest $request): array
    {
        return CommentService::getAll($request);
    }

    public function Create (ChangeStateCommentRequest $request)
    {
        return CommentService::createComment($request);
    }

    public function ChangeState (ChangeStateCommentRequest $request)
    {
        return CommentService::changeState($request);
    }
}