<?php

namespace App\Model;

class Comment extends Model
{
    protected $comment_id;
    protected $thread_id;
    protected $user_id;
    protected $like_count;
    protected $comment;
    protected $photo_url;
    protected $created_at;
    protected $updated_at;
}