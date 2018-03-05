<?php

namespace App\Model;

use App\Traits\TimeElapsed;

class Thread extends Model
{
    use TimeElapsed;

    protected $thread_id;
    protected $comment;
    protected $user_name;
    protected $count;
    protected $updated_at;

    public function updatedAtStr()
    {
        return $this->timeToString(new \DateTime($this->updated_at));
    }

    public function __toString(): string
    {
        return <<<TO_STRING
thread_id: $this->thread_id
comment: $this->comment
user_name: $this->user_name
count: $this->count
updated_at: $this->updated_at
TO_STRING;
    }
}