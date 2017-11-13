<?php

namespace App\Model;

class Comment
{
    private $comment_id;
    private $thread_id;
    private $user_id;
    private $like_count;
    private $comment;
    private $photo_url;
    private $created_at;
    private $updated_at;

    public function __get($key)
    {
        if (property_exists(self::class, $key)) {
            return $this->$key;
        }
        return null;
    }

    public function __set($key, $value)
    {
        if (property_exists(self::class, $key)) {
            $this->$key = $value;
        }
    }

    public function __isset($name)
    {
        return property_exists(self::class, $name);
    }
}