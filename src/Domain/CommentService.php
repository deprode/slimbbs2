<?php

namespace App\Domain;

use App\Exception\DeleteFailedException;
use App\Exception\FetchFailedException;
use App\Exception\SaveFailedException;
use App\Model\Comment;

class CommentService
{
    private $db;

    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }

    public function getComments(int $thread_id = null): array
    {
        $sql = <<<COMMENTS
SELECT
  `comments`.`comment_id`, `comments`.`user_id`, `comments`.`like_count`, `comments`.`comment`, `comments`.`created_at`, `users`.`user_name`, `users`.`user_image_url`
FROM
  `comments`
LEFT JOIN
  `users`
  ON `comments`.`user_id` = `users`.`user_id`
WHERE
  `thread_id` = :thread_id;
COMMENTS;

        try {
            return $this->db->fetchAll($sql, [':thread_id' => ['value' => $thread_id, 'type' => \PDO::PARAM_INT]]);
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }

    public function searchComments(string $query): array
    {
        $sql = <<<COMMENTS
SELECT
  `comments`.`comment_id`, `comments`.`thread_id`, `comments`.`user_id`, `comments`.`like_count`, `comments`.`comment`, `comments`.`created_at`, `users`.`user_name`, `users`.`user_image_url`
FROM
  `comments`
LEFT JOIN
  `users`
  ON `comments`.`user_id` = `users`.`user_id`
WHERE
  `comment` LIKE :query;
COMMENTS;
        try {
            return $this->db->fetchAll($sql, [':query' => ['value' => '%'.$query.'%', 'type' => \PDO::PARAM_STR]]);
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }

    public function saveThread(Comment $comment): int
    {
        $sql = <<<SAVE
INSERT INTO
  `comments` (`thread_id`, `user_id`, `like_count`, `comment`, `photo_url`, `created_at`, `updated_at`)
SELECT
    CASE 
        WHEN MAX(`thread_id`) IS NULL THEN 1
        ELSE MAX(`thread_id`) + 1
    END,
    :user_id, 0, :comment, '', :created_at, NULL
FROM
    `comments`;
SAVE;
        $values = [
            ':user_id'    => ['value' => $comment->user_id, 'type' => \PDO::PARAM_INT],
            ':comment'    => ['value' => $comment->comment, 'type' => \PDO::PARAM_STR],
            ':created_at' => ['value' => date_create()->format('Y-m-d H:i:s'), 'type' => \PDO::PARAM_STR],
        ];

        try {
            return $this->db->execute($sql, $values);
        } catch (\PDOException $e) {
            throw new SaveFailedException();
        }
    }

    public function saveComment(Comment $comment): int
    {
        $sql = <<<SAVE
INSERT INTO
    `comments`
    (`thread_id`, `user_id`, `like_count`, `comment`, `photo_url`, `created_at`, `updated_at`)
VALUES
    (:thread_id, :user_id, 0, :comment, '', :created_at, NULL);
SAVE;
        $values = [
            ':thread_id'  => ['value' => $comment->thread_id, 'type' => \PDO::PARAM_INT],
            ':user_id'    => ['value' => $comment->user_id, 'type' => \PDO::PARAM_INT],
            ':comment'    => ['value' => $comment->comment, 'type' => \PDO::PARAM_STR],
            ':created_at' => ['value' => date_create()->format('Y-m-d H:i:s'), 'type' => \PDO::PARAM_STR],
        ];

        try {
            return $this->db->execute($sql, $values);
        } catch (\PDOException $e) {
            throw new SaveFailedException();
        }
    }

    public function deleteComment(int $comment_id, int $user_id): bool
    {
        $sql = <<<DELETE
DELETE FROM
  `comments`
WHERE
  `comments`.`comment_id` = :comment_id AND `comments`.`user_id` = :user_id;
DELETE;
        $values = [
            ':comment_id' => ['value' => $comment_id, 'type' => \PDO::PARAM_INT],
            ':user_id'    => ['value' => $user_id, 'type' => \PDO::PARAM_INT],
        ];

        try {
            $deleted = $this->db->execute($sql, $values);
        } catch (\PDOException $e) {
            throw new DeleteFailedException();
        }

        return $deleted === 1;
    }

    public function deleteCommentByAdmin(int $comment_id): bool
    {
        $sql = <<<DELETE
DELETE FROM
  `comments`
WHERE
  `comments`.`comment_id` = :comment_id;
DELETE;

        try {
            $deleted = $this->db->execute($sql, [':comment_id' => ['value' => $comment_id, 'type' => \PDO::PARAM_INT]]);
        } catch (\PDOException $e) {
            throw new DeleteFailedException();
        }

        return $deleted === 1;
    }

    public function addLike(int $thread_id, int $comment_id)
    {
        $sql = <<<SQL
UPDATE `comments` SET `like_count` = `like_count` + 1 WHERE `thread_id` = :thread_id AND `comment_id` = :comment_id;
SQL;

        try {
            $updated = $this->db->execute($sql, [
                ':thread_id' => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
                ':comment_id' => ['value' => $comment_id, 'type' => \PDO::PARAM_INT]
            ]);
        } catch (\PDOException $e) {
            throw new SaveFailedException();
        }

        return $updated === 1;
    }
}