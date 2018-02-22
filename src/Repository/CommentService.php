<?php

namespace App\Repository;

use App\Exception\DeleteFailedException;
use App\Exception\FetchFailedException;
use App\Exception\SaveFailedException;
use App\Model\Comment;
use App\Model\Sort;
use App\Service\DatabaseService;
use App\Traits\TimeElapsed;

class CommentService
{
    use TimeElapsed;

    private $db;

    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }

    public function getComments(int $thread_id = null, Sort $sort = null): array
    {
        $sql = <<<COMMENTS
SELECT
  `comments`.`comment_id`, `comments`.`user_id`, `comments`.`like_count`, `comments`.`comment`, `comments`.`photo_url`, `comments`.`created_at`, `users`.`user_name`, `users`.`user_image_url`
FROM
  `comments`
LEFT JOIN
  `users`
  ON `comments`.`user_id` = `users`.`user_id`
WHERE
  `thread_id` = :thread_id
ORDER BY 
  `comments`.`comment_id` {$sort->value()};
COMMENTS;

        try {
            return $this->db->fetchAll($sql, [
                ':thread_id' => ['value' => $thread_id, 'type' => \PDO::PARAM_INT]
            ]);
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }

    public function getCommentsByUser(int $user_id): array
    {
        $sql = <<<COMMENTS
SELECT
  `comments`.`comment_id`, `comments`.`thread_id`, `comments`.`user_id`, `comments`.`like_count`, `comments`.`comment`, `comments`.`photo_url`, `comments`.`created_at`, `users`.`user_name`, `users`.`user_image_url`
FROM
  `comments`
LEFT JOIN
  `users`
  ON `comments`.`user_id` = `users`.`user_id`
WHERE
  `comments`.`user_id` = :user_id
ORDER BY
  `comments`.`comment_id` DESC
COMMENTS;

        try {
            return $this->db->fetchAll($sql, [
                ':user_id' => ['value' => $user_id, 'type' => \PDO::PARAM_INT]
            ]);
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }

    public function convertTime(array $comments = []): array
    {
        for ($i = 0; $i < count($comments); $i++) {
            if (isset($comments[$i]['created_at'])) {
                $comments[$i]['created_at'] = $this->timeToString(new \DateTime($comments[$i]['created_at']));
            }
        }

        return $comments;
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
            return $this->db->fetchAll($sql, [':query' => ['value' => '%' . $query . '%', 'type' => \PDO::PARAM_STR]]);
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }

    public function saveThread(Comment $comment): int
    {

        $add_thread = <<<ADD_THREAD
INSERT INTO
  `threads` (`comment_id`, `user_id`, `updated_at`)
VALUES
  (:comment_id, :user_id, CURRENT_TIMESTAMP);
ADD_THREAD;

        $add_comment = <<<SAVE
INSERT INTO
  `comments` (`thread_id`, `user_id`, `like_count`, `comment`, `photo_url`, `created_at`, `updated_at`)
VALUES 
  (:thread_id, :user_id, 0, :comment, '', :created_at, NULL);
SAVE;
        $update = <<<UPDATE_THREAD
UPDATE
    `threads`
SET
    `comment_id` = :comment_id
WHERE
    `thread_id` = :thread_id;
UPDATE_THREAD;


        try {
            $this->db->beginTransaction();
            $this->db->execute($add_thread, [
                ':comment_id' => ['value' => 1, 'type' => \PDO::PARAM_INT],
                ':user_id'    => ['value' => $comment->user_id, 'type' => \PDO::PARAM_INT],
            ]);

            $thread_id = $this->db->lastInsertId('thread_id');
            $count = $this->db->execute($add_comment, [
                ':thread_id'  => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
                ':user_id'    => ['value' => $comment->user_id, 'type' => \PDO::PARAM_INT],
                ':comment'    => ['value' => $comment->comment, 'type' => \PDO::PARAM_STR],
                ':created_at' => ['value' => date_create()->format('Y-m-d H:i:s'), 'type' => \PDO::PARAM_STR],
            ]);

            $comment_id = $this->db->lastInsertId('comment_id');
            $this->db->execute($update, [
                ':comment_id' => ['value' => $comment_id, 'type' => \PDO::PARAM_INT],
                ':thread_id'  => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
            ]);

            $this->db->commit();
            return $count;
        } catch (\PDOException $e) {
            $this->db->rollback();
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
    (:thread_id, :user_id, 0, :comment, :photo_url, :created_at, NULL);
SAVE;
        $values = [
            ':thread_id'  => ['value' => $comment->thread_id, 'type' => \PDO::PARAM_INT],
            ':user_id'    => ['value' => $comment->user_id, 'type' => \PDO::PARAM_INT],
            ':comment'    => ['value' => $comment->comment, 'type' => \PDO::PARAM_STR],
            ':photo_url'  => ['value' => $comment->photo_url, 'type' => \PDO::PARAM_STR],
            ':created_at' => ['value' => date_create()->format('Y-m-d H:i:s'), 'type' => \PDO::PARAM_STR],
        ];

        $increment_count = <<<THREAD_UPDATE
UPDATE
  `threads` 
SET
  `threads`.`count` = `threads`.`count`+1
WHERE
  `threads`.`thread_id` = :thread_id;
THREAD_UPDATE;

        $thread_update_values = [
            ':thread_id' => ['value' => $comment->thread_id, 'type' => \PDO::PARAM_INT],
        ];

        try {
            $this->db->beginTransaction();
            $saved = $this->db->execute($sql, $values);
            $this->db->execute($increment_count, $thread_update_values);
            $this->db->commit();
            return $saved;
        } catch (\PDOException $e) {
            $this->db->rollback();
            throw new SaveFailedException();
        }
    }

    /**
     * @throws SaveFailedException
     */
    public function updateComment(int $thread_id, int $comment_id, string $comment): int
    {
        $sql = <<<UPDATE_COMMENT
UPDATE
    `comments`
SET
    `comment` = :comment, `updated_at` = :updated_at    
WHERE
    `thread_id` = :thread_id AND `comment_id` = :comment_id;
UPDATE_COMMENT;

        $values = [
            ':thread_id'  => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
            ':comment_id' => ['value' => $comment_id, 'type' => \PDO::PARAM_INT],
            ':comment'    => ['value' => $comment, 'type' => \PDO::PARAM_STR],
            ':updated_at' => ['value' => date_create()->format('Y-m-d H:i:s'), 'type' => \PDO::PARAM_STR],
        ];

        try {
            return $this->db->execute($sql, $values);
        } catch (\PDOException $e) {
            throw new SaveFailedException();
        }
    }

    public function deleteComment(int $thread_id, int $comment_id, int $user_id = 0): bool
    {
        if ($user_id === 0) {
            $delete_comment = <<<DELETE
DELETE FROM
  `comments`
WHERE
  `comments`.`comment_id` = :comment_id;
DELETE;

            $delete_comment_values = [
                ':comment_id' => ['value' => $comment_id, 'type' => \PDO::PARAM_INT],
            ];
        } else {
            $delete_comment = <<<DELETE
DELETE FROM
  `comments`
WHERE
  `comments`.`comment_id` = :comment_id AND `comments`.`user_id` = :user_id;
DELETE;

            $delete_comment_values = [
                ':comment_id' => ['value' => $comment_id, 'type' => \PDO::PARAM_INT],
                ':user_id'    => ['value' => $user_id, 'type' => \PDO::PARAM_INT],
            ];
        }


        $decrement_count = <<<THREAD_UPDATE
UPDATE
  `threads` 
SET
  `threads`.`count` = `threads`.`count`-1
WHERE
  `threads`.`thread_id` = :thread_id;
THREAD_UPDATE;

        $thread_update_values = [
            ':thread_id' => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
        ];

        $delete_thread = <<<DELETE_THREAD
DELETE FROM
  `threads`
WHERE
  `thread_id` = :thread_id AND `count` = 0;
DELETE_THREAD;

        $delete_thread_values = [
            ':thread_id' => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
        ];

        $deleted = 0;
        try {
            $this->db->beginTransaction();
            $deleted = $this->db->execute($delete_comment, $delete_comment_values);
            if ($deleted !== 1) {
                throw new \PDOException();
            }
            $this->db->execute($decrement_count, $thread_update_values);
            $this->db->execute($delete_thread, $delete_thread_values);
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollback();
            throw new DeleteFailedException();
        }

        return $deleted === 1;
    }

    public function deleteCommentByAdmin(int $thread_id, int $comment_id): bool
    {
        return $this->deleteComment($thread_id, $comment_id);
    }

    public function addLike(int $thread_id, int $comment_id): bool
    {
        $sql = <<<SQL
UPDATE
  `comments`
SET
  `like_count` = `like_count` + 1
WHERE
  `thread_id` = :thread_id AND `comment_id` = :comment_id;
SQL;

        try {
            $updated = $this->db->execute($sql, [
                ':thread_id'  => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
                ':comment_id' => ['value' => $comment_id, 'type' => \PDO::PARAM_INT]
            ]);
        } catch (\PDOException $e) {
            throw new SaveFailedException();
        }

        return $updated === 1;
    }
}