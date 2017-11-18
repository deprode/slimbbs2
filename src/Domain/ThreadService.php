<?php

namespace App\Domain;

use App\Model\Thread;

class ThreadService
{
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function getThreads()
    {
        $sql = "SELECT * FROM `threads` LEFT JOIN `comments` ON `threads`.`comment_id` = `comments`.`comment_id`";
        $prepare = $this->db->prepare($sql);
        $prepare->execute();

        $prepare->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, Thread::class);
        $comments = $prepare->fetchAll();
        return $comments;
    }
}