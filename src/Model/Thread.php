<?php

namespace App\Model;

class Thread extends Model
{
    protected $thread_id;
    protected $comment_id;
    protected $user_id;
    protected $comment;
    protected $created_at;

    public function __toString()
    {
        return <<<TO_STRING
thread_id: $this->thread_id
comment_id: $this->comment_id
user_id: $this->user_id
comment: $this->comment
created_at: $this->created_at
TO_STRING;
    }
}