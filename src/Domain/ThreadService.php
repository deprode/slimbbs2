<?php

namespace App\Domain;

use App\Exception\FetchFailedException;

class ThreadService
{
    private $db;

    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }

    public function getSortValue($key = 'new'): string
    {
        $values = ['new' => 'DESC', 'old' => 'ASC'];

        return isset($values[$key]) ? $values[$key] : $values['new'];
    }

    public function getThreads($sort_key = 'new'): array
    {
        $sort_value = $this->getSortValue($sort_key);

        $sql = <<<THREADS
SELECT
  `threads`.`thread_id`, `comments`.`comment`, `comments`.`created_at`, `threads`.`updated_at`
FROM
  `threads`
LEFT JOIN
  `comments`
  ON
    `threads`.`comment_id` = `comments`.`comment_id`
ORDER BY 
  `threads`.`updated_at` $sort_value;
THREADS;

        try {
            return $this->db->fetchAll($sql);
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }
}