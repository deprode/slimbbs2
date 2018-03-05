<?php

namespace App\Repository;

use App\Collection\ThreadCollection;
use App\Exception\FetchFailedException;
use App\Model\Thread;
use App\Service\DatabaseService;
use App\Traits\TimeElapsed;

class ThreadService
{
    use TimeElapsed;

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

    /**
     * @param string $sort_key
     * @return ThreadCollection
     * @throws FetchFailedException
     */
    public function getThreads($sort_key = 'new'): ThreadCollection
    {
        $sort_value = $this->getSortValue($sort_key);

        $sql = <<<THREADS
SELECT
  `threads`.`thread_id`, `comments`.`comment`, `users`.`user_name`, `threads`.`count`, `threads`.`updated_at`
FROM
  `threads`
LEFT JOIN
  `comments`
  ON
    `threads`.`comment_id` = `comments`.`comment_id`
LEFT JOIN
  `users`
  ON
    `users`.`user_id` = `comments`.`user_id`
ORDER BY 
  `threads`.`updated_at` $sort_value;
THREADS;

        try {
            return new ThreadCollection($this->db->fetchAll($sql, [], Thread::class));
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }
}