<?php

namespace App\Domain;

use App\Model\Comment;

class CommentService
{
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function getComments($thread_id = null)
    {
        $sql = 'SELECT `comment`, `created_at` FROM `comments` WHERE `thread_id` = :thread_id';
        $prepare = $this->db->prepare($sql);
        $prepare->bindValue(':thread_id', $thread_id);
        $prepare->execute();

        $prepare->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, Comment::class);
        $comments = $prepare->fetchAll();
        return $comments;
    }

    public function saveThread(Comment $comment)
    {
        $sql = <<<SAVE
INSERT INTO `comments` (`thread_id`, `user_id`, `like_count`, `comment`, `photo_url`, `created_at`, `updated_at`)
SELECT
    CASE 
        WHEN MAX(`thread_id`) IS NULL THEN 1
        ELSE MAX(`thread_id`) + 1
    END,
    0, 0, :comment, '', :created_at, NULL
FROM
    `comments`;
SAVE;
        $prepare = $this->db->prepare($sql);
        $prepare->bindValue(':comment', $comment->comment);
        $prepare->bindValue(':created_at', date_create()->format('Y-m-d H:i:s'));
        $prepare->execute();
    }

    public function saveComment(Comment $comment)
    {
        $sql = <<<SAVE
INSERT INTO
    `comments`
    (`thread_id`, `user_id`, `like_count`, `comment`, `photo_url`, `created_at`, `updated_at`)
VALUES
    (:thread_id, :user_id, 0, :comment, '', :created_at, NULL);
SAVE;
        $prepare = $this->db->prepare($sql);
        $prepare->bindValue(':thread_id', $comment->thread_id);
        $prepare->bindValue(':user_id', $comment->user_id);
        $prepare->bindValue(':comment', $comment->comment);
        $prepare->bindValue(':created_at', date_create()->format('Y-m-d H:i:s'));
        $prepare->execute();
    }
}