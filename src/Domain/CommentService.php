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
}