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

    public function __toString(): string
    {
        return <<<TO_STRING
comment_id: $this->comment_id
thread_id: $this->thread_id
user_id: $this->user_id
like_count: $this->like_count
comment: $this->comment
photo_url: $this->photo_url
created_at: $this->created_at
updated_at: $this->updated_at
TO_STRING;
    }
}