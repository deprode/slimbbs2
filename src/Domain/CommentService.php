<?php

namespace App\Domain;

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

        return $prepare->fetchAll(\PDO::FETCH_ASSOC);
    }
}