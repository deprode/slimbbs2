<?php


namespace App\Model;


use App\Traits\TimeElapsed;

class CommentRead extends Model
{
    use TimeElapsed;

    protected $comment_id;
    protected $thread_id;
    protected $user_id;
    protected $like_count;
    protected $comment;
    protected $photo_url;
    protected $created_at;
    protected $updated_at;
    protected $user_name;
    protected $user_image_url;

    public function createdAtStr()
    {
        return $this->timeToString(new \DateTime($this->created_at));
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function __toString()
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
user_name: $this->user_name
user_image_url: $this->user_image_url
TO_STRING;
    }
}