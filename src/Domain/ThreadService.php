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

    public function getThreads(): array
    {
        $sql = <<<THREADS
SELECT
  `threads`.`thread_id`, `comment`, `created_at`
FROM
  `threads`
LEFT JOIN
  `comments`
  ON
    `threads`.`comment_id` = `comments`.`comment_id`;
THREADS;

        $prepare->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, Thread::class);
        $comments = $prepare->fetchAll();
        return $comments;
    }
}