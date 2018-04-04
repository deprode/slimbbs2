<?php

namespace App\Repository;

use App\Exception\DeleteFailedException;
use App\Exception\FetchFailedException;
use App\Exception\SaveFailedException;
use App\Model\Comment;
use App\Model\CommentRead;
use App\Model\Sort;
use App\Service\DatabaseService;
use App\Traits\TimeElapsed;
use Aura\SqlQuery\QueryFactory;

class CommentService
{
    use TimeElapsed;

    private $db;
    private $query;
    private $comment_limit;

    /**
     * CommentService constructor.
     * @param DatabaseService $db
     * @param QueryFactory $query
     * @param int $comment_limit
     */
    public function __construct(DatabaseService $db, QueryFactory $query, int $comment_limit)
    {
        $this->db = $db;
        $this->query = $query;
        $this->comment_limit = $comment_limit;
    }

    /**
     * @param int|null $thread_id
     * @param Sort|null $sort
     * @return array
     * @throws FetchFailedException
     */
    public function getComments(int $thread_id = null, Sort $sort = null): array
    {
        $select = $this->query->newSelect();
        $select
            ->from('comments')
            ->cols(['comments.comment_id', 'comments.user_id', 'comments.like_count', 'comments.comment', 'comments.photo_url', 'comments.created_at', 'users.user_name', 'users.user_image_url'])
            ->join('left', 'users', 'comments.user_id = users.user_id')
            ->where('thread_id = :thread_id')
            ->orderBy(['comments.comment_id ' . $sort->value()]);

        try {
            return $this->db->fetchAll($select->getStatement(), [
                ':thread_id' => ['value' => $thread_id, 'type' => \PDO::PARAM_INT]
            ], CommentRead::class);
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }

    /**
     * @param int $user_id
     * @param bool $limit
     * @return array
     * @throws FetchFailedException
     */
    public function getCommentsByUser(int $user_id, bool $limit): array
    {
        $select = $this->query->newSelect();
        $select
            ->from('comments')
            ->cols(['comments.comment_id, comments.thread_id, comments.user_id, comments.like_count, comments.comment, comments.photo_url, comments.created_at, users.user_name, users.user_image_url'])
            ->join('left', 'users', 'comments.user_id = users.user_id')
            ->where('comments.user_id = :user_id')
            ->orderBy(['comments.comment_id DESC']);

        $params = [
            ':user_id' => ['value' => $user_id, 'type' => \PDO::PARAM_INT]
        ];

        if ($limit) {
            $select->limit($this->comment_limit);
        }

        try {
            return $this->db->fetchAll($select->getStatement(), $params, CommentRead::class);
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }

    /**
     * @param string $query
     * @return array
     * @throws FetchFailedException
     */
    public function searchComments(string $query): array
    {
        $select = $this->query->newSelect();
        $select
            ->from('comments')
            ->cols(['comments.comment_id', 'comments.thread_id', 'comments.user_id', 'comments.like_count', 'comments.comment', 'comments.created_at', 'users.user_name', 'users.user_image_url'])
            ->join('left', 'users', 'comments.user_id = users.user_id')
            ->where('comment LIKE :query');

        try {
            return $this->db->fetchAll($select->getStatement(), [
                ':query' => ['value' => '%' . $query . '%', 'type' => \PDO::PARAM_STR]
            ], CommentRead::class);
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }

    /**
     * @param Comment $comment
     * @return int
     * @throws SaveFailedException
     */
    public function saveThread(Comment $comment): int
    {
        $add_thread = $this->query->newInsert();
        $add_thread
            ->into('threads')
            ->cols(['comment_id', 'user_id']);

        $add_comment = $this->query->newInsert();
        $add_comment
            ->into('comments')
            ->cols(['thread_id', 'user_id', 'like_count', 'comment', 'photo_url', 'created_at']);

        $update = $this->query->newUpdate();
        $update
            ->table('threads')
            ->set('comment_id', ':comment_id')
            ->where('thread_id = :thread_id');

        try {
            $this->db->beginTransaction();
            $this->db->execute($add_thread->getStatement(), [
                ':comment_id' => ['value' => 1, 'type' => \PDO::PARAM_INT],
                ':user_id'    => ['value' => $comment->user_id, 'type' => \PDO::PARAM_INT],
            ]);

            $thread_id = $this->db->lastInsertId('thread_id');
            $count = $this->db->execute($add_comment->getStatement(), [
                ':thread_id'  => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
                ':user_id'    => ['value' => $comment->user_id, 'type' => \PDO::PARAM_INT],
                ':like_count' => ['value' => 0, 'type' => \PDO::PARAM_INT],
                ':comment'    => ['value' => $comment->comment, 'type' => \PDO::PARAM_STR],
                ':photo_url'  => ['value' => '', 'type' => \PDO::PARAM_STR],
                ':created_at' => ['value' => date_create()->format('Y-m-d H:i:s'), 'type' => \PDO::PARAM_STR]
            ]);

            $comment_id = $this->db->lastInsertId('comment_id');
            $this->db->execute($update->getStatement(), [
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

    /**
     * @param Comment $comment
     * @return int
     * @throws SaveFailedException
     */
    public function saveComment(Comment $comment): int
    {
        $insert = $this->query->newInsert();
        $insert
            ->into('comments')
            ->cols(['thread_id', 'user_id', 'comment', 'photo_url', 'created_at']);

        $values = [
            ':thread_id'  => ['value' => $comment->thread_id, 'type' => \PDO::PARAM_INT],
            ':user_id'    => ['value' => $comment->user_id, 'type' => \PDO::PARAM_INT],
            ':comment'    => ['value' => $comment->comment, 'type' => \PDO::PARAM_STR],
            ':photo_url'  => ['value' => $comment->photo_url, 'type' => \PDO::PARAM_STR],
            ':created_at' => ['value' => date_create()->format('Y-m-d H:i:s'), 'type' => \PDO::PARAM_STR],
        ];

        $update = $this->query->newUpdate();
        $update
            ->table('threads')
            ->set('count', 'count + 1')
            ->set('updated_at', ':datetime')
            ->where('thread_id = :thread_id');

        $thread_update_values = [
            ':thread_id' => ['value' => $comment->thread_id, 'type' => \PDO::PARAM_INT],
            ':datetime'  => ['value' => date_create()->format('Y-m-d H:i:s'), 'type' => \PDO::PARAM_STR]
        ];

        try {
            $this->db->beginTransaction();
            $this->db->execute($insert->getStatement(), $values);
            $comment_id = $this->db->lastInsertId('comment_id');
            $this->db->execute($update->getStatement(), $thread_update_values);
            $this->db->commit();
            return $comment_id;
        } catch (\PDOException $e) {
            $this->db->rollback();
            throw new SaveFailedException();
        }
    }

    /**
     * @param int $thread_id
     * @param int $comment_id
     * @param string $comment
     * @return int
     * @throws SaveFailedException
     */
    public function updateComment(int $thread_id, int $comment_id, string $comment): int
    {
        $update = $this->query->newUpdate();
        $update
            ->table('comments')
            ->set('comment', ':comment')
            ->set('updated_at', ':updated_at')
            ->where('thread_id = :thread_id')
            ->where('comment_id = :comment_id');

        $values = [
            ':thread_id'  => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
            ':comment_id' => ['value' => $comment_id, 'type' => \PDO::PARAM_INT],
            ':comment'    => ['value' => $comment, 'type' => \PDO::PARAM_STR],
            ':updated_at' => ['value' => date_create()->format('Y-m-d H:i:s'), 'type' => \PDO::PARAM_STR],
        ];

        try {
            return $this->db->execute($update->getStatement(), $values);
        } catch (\PDOException $e) {
            throw new SaveFailedException();
        }
    }

    /**
     * @param int $thread_id
     * @param int $comment_id
     * @param int $user_id
     * @return bool
     * @throws DeleteFailedException
     */
    public function deleteComment(int $thread_id, int $comment_id, int $user_id = 0): bool
    {
        $delete_comment = $this->query->newDelete();

        $delete_comment
            ->from('comments')
            ->where('comment_id = :comment_id');

        $delete_comment_values = [
            ':comment_id' => ['value' => $comment_id, 'type' => \PDO::PARAM_INT],
        ];
        // user is not admin
        if ($user_id > 0) {
            $delete_comment->where('comments.user_id = :user_id');
            $delete_comment_values[':user_id'] = ['value' => $user_id, 'type' => \PDO::PARAM_INT];
        }

        $decrement_count = $this->query->newUpdate();
        $decrement_count
            ->table('threads')
            ->set('count', 'count - 1')
            ->where('thread_id = :thread_id');

        $thread_update_values = [
            ':thread_id' => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
        ];

        $delete_thread = $this->query->newDelete();
        $delete_thread
            ->from('threads')
            ->where('thread_id = :thread_id')
            ->where('count = 0');

        $delete_thread_values = [
            ':thread_id' => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
        ];

        try {
            $this->db->beginTransaction();
            $deleted = $this->db->execute($delete_comment->getStatement(), $delete_comment_values);
            if ($deleted !== 1) {
                throw new \PDOException();
            }
            $this->db->execute($decrement_count->getStatement(), $thread_update_values);
            $this->db->execute($delete_thread->getStatement(), $delete_thread_values);
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollback();
            throw new DeleteFailedException();
        }

        return $deleted === 1;
    }

    /**
     * @param int $thread_id
     * @param int $comment_id
     * @return bool
     * @throws DeleteFailedException
     */
    public function deleteCommentByAdmin(int $thread_id, int $comment_id): bool
    {
        return $this->deleteComment($thread_id, $comment_id);
    }

    /**
     * @param int $thread_id
     * @param int $comment_id
     * @return bool
     * @throws SaveFailedException
     */
    public function addLike(int $thread_id, int $comment_id): bool
    {
        $update = $this->query->newUpdate();
        $update
            ->table('comments')
            ->set('like_count', 'like_count + 1')
            ->where('thread_id = :thread_id AND comment_id = :comment_id');

        try {
            $updated = $this->db->execute($update->getStatement(), [
                ':thread_id'  => ['value' => $thread_id, 'type' => \PDO::PARAM_INT],
                ':comment_id' => ['value' => $comment_id, 'type' => \PDO::PARAM_INT]
            ]);
        } catch (\PDOException $e) {
            throw new SaveFailedException();
        }

        return $updated === 1;
    }
}