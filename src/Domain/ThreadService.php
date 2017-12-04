<?php

namespace App\Domain;

use App\Model\Thread;

class ThreadService
{
    private $db;

    public function __construct(DatabaseService $db)
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

        return $this->db->fetchAll($sql);
    }
}