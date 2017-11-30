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

    public function getComments(int $thread_id = null)
    {
        $sql = <<<COMMENTS
SELECT
  `comments`.`comment_id`, `comments`.`user_id`, `comments`.`comment`, `comments`.`created_at`, `users`.`user_name`, `users`.`user_image_url`
FROM
  `comments`
LEFT JOIN
  `users` ON `comments`.`user_id` = `users`.`user_id`
WHERE `thread_id` = :thread_id;
COMMENTS;
        $prepare = $this->db->prepare($sql);
        $prepare->bindValue(':thread_id', $thread_id, \PDO::PARAM_INT);
        $prepare->execute();

        $prepare->setFetchMode(\PDO::FETCH_ASSOC | \PDO::FETCH_PROPS_LATE);
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
    :user_id, 0, :comment, '', :created_at, NULL
FROM
    `comments`;
SAVE;
        $prepare = $this->db->prepare($sql);
        $prepare->bindValue(':user_id', $comment->user_id, \PDO::PARAM_INT);
        $prepare->bindValue(':comment', $comment->comment, \PDO::PARAM_STR);
        $prepare->bindValue(':created_at', date_create()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
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
        $prepare->bindValue(':thread_id', $comment->thread_id, \PDO::PARAM_INT);
        $prepare->bindValue(':user_id', $comment->user_id, \PDO::PARAM_INT);
        $prepare->bindValue(':comment', $comment->comment, \PDO::PARAM_STR);
        $prepare->bindValue(':created_at', date_create()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $prepare->execute();
    }

    public function deleteComment(int $comment_id, int $user_id)
    {
        $sql = <<<DELETE
DELETE FROM `comments` WHERE `comments`.`comment_id` = :comment_id AND `comments`.`user_id` = :user_id;
DELETE;
        $delete = $this->db->prepare($sql);
        $delete->bindValue(':comment_id', $comment_id, \PDO::PARAM_INT);
        $delete->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
        $delete->execute();

        return ($delete->rowCount() === 1);
    }

    public function deleteCommentByAdmin(int $comment_id)
    {
        $sql = <<<DELETE
DELETE FROM `comments` WHERE `comments`.`comment_id` = :comment_id;
DELETE;
        $delete = $this->db->prepare($sql);
        $delete->bindValue(':comment_id', $comment_id, \PDO::PARAM_INT);
        $delete->execute();

        return ($delete->rowCount() === 1);
    }
}