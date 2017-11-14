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

    public function getComments()
    {
        $sql = 'SELECT * FROM `comments`';
        $prepare = $this->db->prepare($sql);
        $prepare->execute();

        $prepare->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, Comment::class);
        $comments = $prepare->fetchAll();
        return $comments;
    }

    public function saveComment(Comment $comment)
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
}